<?php

namespace app\common\model\imchat;

use think\Model;
use think\Db;
use app\common\model\imchat\RoomUser;
use app\common\model\imchat\Room;
use app\common\model\imchat\GroupMessageStatus;
use \GatewayWorker\Lib\Gateway;

class Message extends Model
{
    // 表名
    protected $name = 'imchat_message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'),'1' => __('Status 1')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function room()
    {
        return $this->belongsTo('Room', 'room_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 根据请求用户查询其未读信息
     * 群组 or 普通人员
     * @param  int $uid 用户id
     * @return array
     */
    public static function getNoReadByUser($uid)
    {
        // 未读普通聊天
        $all = self::field('room_id, from_uid, content, createtime')
            ->where("`room_id` =0 AND `status`='0' AND `to_uid` = {$uid}")
            ->order('id', 'desc')
            ->select();

        $tag = [];
        $ret = [];
        // 个人聊天
        foreach ($all as $v) {
            if (!in_array($v->from_uid, $tag)) {
                array_push($tag, $v->from_uid);
                $ret[] = [
                    'type' => '',
                    'id'=> $v->from_uid,
                    'nickname'=> self::getUserBaseInfo($v->from_uid)['username'],
                    'username'=> self::getUserBaseInfo($v->from_uid)['nickname'],
                    'avatar'=> self::getUserBaseInfo($v->from_uid)['avatar'],
                    'last_msg'=> $v->content,
                    'timestamp'=> $v->createtime,
                    'unread_count' => self::where('room_id', 0)
                            ->where('status', '0')
                            ->where('from_uid', $v->from_uid)
                            ->where('to_uid', $uid)
                            ->count()
                ];
            }
        }

        // 处理群组消息 先取出所有群组 然后计算有没有未读聊天信息 有的话取出
        foreach (RoomUser::getRoomIdByUser($uid) as $room_id) {
            // 先判断是否全部已读 没有读取才取出
            $_count = GroupMessageStatus::getChatGroupCount($uid, $room_id);
            if ($_count) {
                $room = Room::find($room_id);
                // 查询最后一条记录
                $d= self::where('room_id', $room_id)
                    ->order('id', 'desc')
                    ->find();
                $ret[] = [
                    'type' => 'group',
                    'id'=> $room_id,
                    'nickname' => $room->title,
                    'username' => $room->title,
                    'avatar' => $room->img,
                    'unread_count' => $_count,
                    'last_msg'=> $d['content'],
                    'timestamp'=> $d['createtime'],
                ];
            }
        }
        // 排个序?
        //根据字段createtime对数组$data进行降序排列
        $_arr = array_column($ret,'timestamp');
        array_multisort($_arr,SORT_DESC,$ret);

        return $ret;
    }

    /**
     * 获取用户基本信息
     * @param  int $uid 用户id
     * @return array 
     */
    private static function getUserBaseInfo($uid)
    {
        static $users=[];
        if (!array_key_exists($uid, $users)) {
            $users[$uid] = Db::name('user')->field('id uid, username, nickname, avatar,bio')
                ->find($uid);
        }
        return $users[$uid];
    }

    /**
     * 插入消息
     * @param  [type] $data 消息内容
     * @param  [type] $uid  用户id
     * @return [boolean]      结果
     */
    public static function insertMsg ($data, $uid)
    {
        extract($data);
        // 过滤脚本
        $preg = "/(<script[\s\S]*?<\/script>)/i";
        $content = preg_replace_callback ($preg, function ($matches) {
            return htmlentities($matches[1]);
        } , $content);
        // 群组
        if ($type == 'group') {
            // 判断是否有权限插入 大于100的群组才需要配置人员
            if ($to_uid>= 100) {
                $hasGroup = RoomUser::getRoomIdByUser($from_uid);
                if (!in_array($to_uid, $hasGroup)) {
                    return false;
                }
            }
            $ret = self::insertGetId([
                'room_id'=> $to_uid,
                'from_uid'=> $from_uid,
                'status'=> 1,
                'createtime'=> time(),
                'content'=> $content
            ]);
            // 此处还要标记自己群组已读 个人的无需维护 机制不同
            GroupMessageStatus::refreshUserRead($uid, $to_uid, $ret);
        } else {
            // 人员
            $ret = self::insertGetId([
                'to_uid'=> $to_uid,
                'from_uid'=> $from_uid,
                'createtime'=> time(),
                'content'=> $content
            ]);
        }
        if ($ret) {
            $rt =self::_makeChat_record($type, $from_uid, $to_uid, $content, time(), $ret);
            // 发送消息
            if ($type== 'group') {
                // 排除自己
                $exclude_client = Gateway::getClientIdByUid($from_uid);
                $mdata = json_encode([
                        'type'=> 'html',
                        'data'=> $rt,
                    ]);
                // 发送群组还是所有
                if ($to_uid >= 100) {
                    Gateway::sendToGroup($to_uid, $mdata, $exclude_client);
                }else {
                    Gateway::sendToAll($mdata, null, $exclude_client);
                }
            }
            else {
                // 自己的话无需发送
                if ($from_uid != $to_uid) {
                    Gateway::sendToUid($to_uid, json_encode([
                        'type'=> 'html',
                        'data'=> $rt,
                    ]));
                }
            }
        }
        return $ret ? $rt : false;
    }

    /**
     * 
     */
    /**
     * 生成聊天记录格式 推送给用户
     * @param  String $type       群组or个人
     * @param  integer $from_uid   发送人id
     * @param  integer $to_uid     接收人id 可能是群组id
     * @param  String $content    内容
     * @param  integer $createtime 时间
     * @return array
     */
    private static function _makeChat_record ($type, $from_uid, $to_uid, $content, $createtime, $msg_id)
    {
        return [
                'from_uid'=> $from_uid,
                'type' => $type,
                'to_uid' => $to_uid,
                'createtime'=> $createtime,
                'content'=> $content,
                'userInfo'=> self::getUserBaseInfo($from_uid),
                'msg_id'=> $msg_id,
            ];
    }

    /**
     * 登录时获取未读的聊天记录
     * 分群组和个人存储
     * @param  int $uid [description]
     * @return [type]      [description]
     */
    public static function getNoReadMsg ($rec_chat ,$uid)
    {
        $ret = [];
        foreach ($rec_chat as $value) {
            // 获取群组聊天记录 群组以group_前缀打头 最多取30条！
            if ($value['type'] == 'group') {
                $no_read = self::field('id msg_id, room_id id, from_uid, content, createtime')
                    ->where([
                        'room_id'=> $value['id'],
                        'id' => ['GT', GroupMessageStatus::readPosByUser($uid, $value['id'])]
                    ])
                    ->order('msg_id desc')
                    ->limit(30)
                    ->select();
                $no_read = array_reverse($no_read);
                // 将每一条加上用户信息等处理
                $ret['group_'.$value['id']] = array_map(function($v){
                    return self::_makeChat_record('group', $v['from_uid'], 0, $v['content'], $v['createtime'], $v['msg_id']);
                }, $no_read);
            } else {
                $no_read = self::field('id msg_id,to_uid, from_uid, content, createtime')
                    ->where([
                        'to_uid'=> $uid,
                        'status'=> "0",
                        'from_uid'=> $value['id']
                    ])
                    ->select();
                $ret[$value['id']] = array_map(function($v){
                    return self::_makeChat_record('', $v['from_uid'], 0, $v['content'], $v['createtime'], $v['msg_id']);
                }, $no_read);
            }
        }
        return $ret;
    }
    /**
     * 设置已读
     * @param [type] $uid  用户
     * @param [type] $id   房间或者用户id
     * @param [type] $type 类别 普通 或者群组
     */
    public static function setRead ($uid, $id, $type) {
        if ($type == 'group') {
            // 先查询此群最后一条记录id
            $d= self::field('id')
                ->where('room_id', $id)
                ->order('id', 'desc')
                ->find();
            // 刷新其已读到的群组id
            GroupMessageStatus::refreshUserRead($uid, $id, $d['id']);
        } 
        else {
            self::where("from_uid", $id)
                ->where('to_uid', $uid)
                ->where('room_id', 0)
                ->setField('status', '1');
        }
    }

    /**
     * 获取历史记录
     * @param  [type] $data 比如最后一个id 类别
     * @param  [type] $uid  用户
     * @return [array]       记录数组
     */
    public static function getHistoryChatRecord ($data, $uid) {
        extract($data);
        if ($type == 'group') {
            $where['room_id'] = $id;
            if ($msg_id) {
                $where['id'] = ['LT', $msg_id];
            }
            // 计算是否还有更多
            $_count = self::where($where)->count();
            $ret['has_more'] = $_count > 15 ? true : false;
            $_rt = self::field('id msg_id, room_id id, from_uid, content, createtime')
                ->where($where)
                ->order('msg_id', 'DESC')
                ->limit(15)
                ->select();
            // 将每一条加上用户信息等处理
            $ret['data'] = array_map(function($v){
                return self::_makeChat_record('group', $v['from_uid'], 0, $v['content'], $v['createtime'], $v['msg_id']);
            }, $_rt);
        } else {
            $where['to_uid'] = $uid;
            $where['from_uid'] = $id;
            if ($msg_id) {
                $where['id'] = ['LT', $msg_id];
            }
            $_count = self::where($where)->count();
            $ret['has_more'] = $_count > 15 ? true : false;
            $_rt = self::field('id msg_id,to_uid, from_uid, content, createtime')
                    ->where($where)
                    ->order('msg_id', 'DESC')
                    ->limit(15)
                    ->select();
            $ret['data'] = array_map(function($v){
                    return self::_makeChat_record('', $v['from_uid'], 0, $v['content'], $v['createtime'], $v['msg_id']);
                }, $_rt);
        }
        // 倒序 数据需要
        $ret['data'] = array_reverse($ret['data']);
        return $ret;
    }
}
