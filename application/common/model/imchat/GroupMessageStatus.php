<?php

namespace app\common\model\imchat;

use think\Model;
use think\Db;

class GroupMessageStatus extends Model
{
    // 表名
    protected $name = 'imchat_group_message';


    /**
     * 获取请求用户 是否已读群组消息 多少条未读
     * @param  int $uid
     * @param  int $room_id
     * @return array
     */
    public static function getChatGroupCount($uid, $room_id)
    {
        // 先取出此群组已读到的位置id
        $last_id = self::readPosByUser($uid, $room_id);
        return Db::name('imchat_message')
            ->where([
                'room_id'=> $room_id,
                'id' => ['>', $last_id]
            ])
            ->count();
    }

    /**
     * 用户阅读到某个群组的信息id
     * @param int $uid    
     * @param  int $room_id
     * @return int         
     */
    public static function readPosByUser($uid, $room_id)
    {
        $ret = self::where('room_id', $room_id)
            ->where('user_id', $uid)
            ->find();
        return $ret ? $ret['last_message_id'] : 0;
    }

    /**
     * 更新用户某个群组已读id
     * @param  [type] $uid     用户id
     * @param  [type] $room_id 房间
     * @param  [type] $last_id 已读的最后一条消息
     */
    public static function refreshUserRead ($uid, $room_id, $last_id)
    {
        // 存储到最后一条已读 先删除 再插入
        self::where('room_id', $room_id)
            ->where('user_id', $uid)
            ->delete();
        self::insert([
            'room_id'=> $room_id,
            'user_id'=> $uid,
            'last_message_id'=> $last_id,
        ]);
    }

}
