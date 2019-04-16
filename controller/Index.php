<?php

namespace addons\imchat\controller;

use think\addons\Controller;
use app\common\model\imchat\RoomUser;
use app\common\model\imchat\Room;
use app\common\model\imchat\Message;
use think\Db;
use \GatewayWorker\Lib\Gateway;
use \think\Validate;

class Index extends Controller
{
    /**
     * 限制已登录
     */
    public function _initialize()
    {
        parent::_initialize();
        if (!$this->auth->id) {
            header('Content-Type: application/json;');
            echo json_encode([
                'errno'=> 403,
                'msg'=> '请先登录'
            ]);
            die;
        }
    }
    /**
     * 首页 用于测试
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 聊天用户列表
     */
    public function chatList()
    {
        // 找出可以聊天的所有用户
        $users = RoomUser::getChatUsersList();
        // 找出加入的所有群组
        $chat_group = Room::getChatGroup($this->auth->id);
        // 找出此人所有的未读信息 放到最近聊天名单
        $rec_chat = Message::getNoReadByUser($this->auth->id);
        $chat_record = Message::getNoReadMsg($rec_chat, $this->auth->id);
        $ret = [
            'user_list'=> $users,
            'chat_group'=> $chat_group,
            'rec_chat'=> $rec_chat,
            'chat_record'=> $chat_record,
        ];
        return json($ret);
    }

    /**
     * 获取用户信息以及凭证 socket地址
     * @return json
     */
    public function cert ()
    {
        $cert = time().array_reduce(range(1, 158), function($v1, $v2) {
                $s = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                return $v1.$s[rand(0,61)];
            });
        Db::name('imchat_cert')->insert([
            'cert'=> $cert,
            'expire_time'=> time()+10 //10秒内有效
        ]);
        // 几率删除以前的
        if (rand(0,100) == 1) {
            Db::name('imchat_cert')->where(['expire_time'=> ['LT', time()]])->delete();
        }
        $ret['user'] = [
            'id' => $this->auth->id,
            'username'=> $this->auth->username,
            'nickname'=> $this->auth->nickname,
            'avatar'=> $this->auth->avatar,
            'cert'=> $cert,
        ];
        $ret['socket_addr'] = "ws://localhost:7887";
        return json($ret);
    }

    /**
     * 为websocket绑定用户
     */
    public function bindWSUser()
    {
        $client_id = input('client_id');
        $cert = input('cert');
        if ($client_id && $cert) {
            // 先判断是否有绑定权限
            $where=[
                'cert'=> $cert,
                'expire_time'=> ['EGT', time()]
            ];
            $has = Db::name('imchat_cert')->where($where)->find();
            if ($has) {
                // 执行uid绑定
                Gateway::bindUid($client_id, $this->auth->id);
                // 群组加入
                foreach (RoomUser::getRoomIdByUser($this->auth->id) as $value) {
                    Gateway::joinGroup($client_id, $value);
                }
                //删除
                Db::name('imchat_cert')->where($where)->delete();
                return $this->json();
            }
        }
        return $this->json(403, '绑定失败');
    }

    /**
     * 发送文本信息
     */
    public function sendTxtMsg ()
    {
        $data = input('post.');
        $data['content'] = json_decode(file_get_contents('php://input'), true)['content'];
        $validate = new Validate([
            'from_uid'  => 'require|number',
            'to_uid'  => 'number',
            'content' => 'require|max:1000',
        ]);
        if (!$validate->check($data)) {
            return $this->json(500, $validate->getError());
        }
        // 插入
        $ret = Message::insertMsg($data, $this->auth->id);
        return $ret ? $this->json($ret) : $this->json(500, '发送失败');
    }

    private function json ($data=[], $errno=0, $msg='ok')
    {
        if (is_array($data) && $data) {
            return json([
                'errno'=> $errno,
                'msg'=> $msg,
                'data'=> $data,
            ]);
        }
        $_errno = $errno;
        $errno = $data ? $data : $errno;
        $msg = $_errno !==0 ? $_errno : $msg;
        return json([
            'errno'=> $errno,
            'msg'=> $msg
        ]);
    }


    /**
     * 获取聊天记录
     */
    public function getHistoryChatRecord ()
    {
        $data = input("post.");
        if (!isset($data['msg_id']))
        $data['msg_id'] = isset($data['msg_id']) ? (int)$data['msg_id'] : 0;
        $rule = [
            'id'=> 'require|number',
        ];
        $result = $this->validate($data, $rule);
        if ($result !== true) {
            return $this->json(500, $result);
        }
        $ret = Message::getHistoryChatRecord($data, $this->auth->id);
        return $this->json($ret);
    }
    /**
     * 已读标记
     */
    public function setRead ()
    {
        $data = input("post.");
        $rule = [
            'id'=> 'require|number',
        ];
        $result = $this->validate($data, $rule);
        if ($result !== true) {
            return $this->json(500, $result);
        }
        Message::setRead($this->auth->id,$data['id'], $data['type']);
    }

    /**
     * 上传文件或者图片 没有做权限处理 没有考虑用户能否下载问题
     * 没有保留源文件名
     * @return [type] [description]
     */
    public function upload_file()
    {
        $file = request()->file('file');
        if (!$file) {
            return $this->json(403, '没有选择文件或者文件过大');
        }
        // 文件类型是文件还是图片
        $type = in_array($file->getInfo()['type'], ['image/gif', 'image/jpeg', 'image/png']) ? 'image': 'file';
        // 保存路径 注意目录有没有 或者有没有操作权限
        $path = ROOT_PATH . 'public' . DS . 'uploads'. DS . 'imchat';
        if (!file_exists($path)) {
            mkdir($path,0777, true);
        }
        // 10m限制
        $info = $file->validate(['size'=>1024*1024*10])->move($path);
        if ($info) {
            $sn = str_replace('\\', '/', $info->getSaveName());
            if ($type == 'image') {
                // 拼接图片显示字符串
                $sn = '/uploads/imchat/'.$sn;
                $re_str = "<img src='{$sn}'>";
            } else {
                $sn = '/addons/imchat/index/download_file?name='.$sn;
                $re_str = <<<Eof
<div style="font-size: 12px;color: #aaa;">文件</div>
<div style="align-items: center;display: flex;">
  <span class="iconfont icon-3801wenjian" style="font-size: 28px;"></span>
  <a href="{$sn}" style="cursor: pointer;margin-left: 5px;">点击下载</a>
</div>
Eof;
            }
            // 发送消息
            $data = input('post.');
            $data['content'] = $re_str;
            $_ret = Message::insertMsg($data, $this->auth->id);
            if ($_ret) {
                return $this->json($_ret);
            }
            return $this->json(500, '发送失败');
        } else {
            return $this->json(500, $file->getError());
        }
    }
    /**
     * 文件下载
     */
    public function download_file() {
        $f = $_REQUEST['name'];
        if (!$f) {
            die('404');
        }
        $name = explode('/', $f);
        $name = end($name);

        $file_name = getcwd().'/uploads/imchat/'.$f;
        if (!file_exists($file_name)) {
            die;
        }
        $file=fopen($file_name,"r");
        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($file_name));
        header("Content-Disposition: attachment; filename=".$name);
        echo fread($file,filesize($file_name));
        fclose($file);
    }
}
