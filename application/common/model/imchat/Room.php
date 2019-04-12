<?php

namespace app\common\model\imchat;

use think\Model;
use think\Db;

class Room extends Model
{
    // 表名
    protected $name = 'imchat_room';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
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


    /**
     * 获取请求用户已加入的可聊天的群组
     * @param  int $uid 用户id
     * @return array
     */
    public static function getChatGroup($uid)
    {
        $ret = self::field('id, title name, intro, img avatar')
            ->where('status', "1")
            ->whereIn('id', RoomUser::getRoomIdByUser($uid))
            ->select();
        return array_map(function($v) {
            $v = $v->toArray();
            unset($v['status_text']);
            $v['type'] = 'group';
            return $v;
        }, $ret);
    }

}
