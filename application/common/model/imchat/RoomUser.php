<?php

namespace app\common\model\imchat;

use think\Model;
use \think\Db;

class RoomUser extends Model
{
    // 表名
    protected $name = 'imchat_room_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = null;
    
    /**
     * 按分组获取所有的用户提供聊天室分配 后台分配
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public static function getAllUserToSelect($ids) 
    {
        $user_ret = [];
        // 获取所有的前台用户 按类别
        $user_groups = Db::name('user_group')->field('id, name')->select();
        foreach ($user_groups as $v) {
            $user_ret[] = 
            [
                "id" => "group_{$v['id']}",
                "parent"=> "#",
                "text"=> $v['name'],
                "state"=> [
                    "selected"=> false
            ]];
            $users = Db::name('user')->field('id, username,nickname')->where('group_id', $v['id'])->select();
            foreach ($users as $value) {
                $user_ret[] = 
                [
                    "id" => $value['id'],
                    "parent"=> "group_{$v['id']}",
                    "text"=> $value['username']."@".$value['nickname'],
                    "state"=> [
                        "selected"=>  self::where('uid', $value['id'])->where('room_id', $ids)->find() ? true : false //是否在此聊天是内
                ]];
            }
        }
        return $user_ret;
    }

    /**
     * 找出可以聊天的所有用户 默认全员可以聊天 添加type
     * @return array
     */
    public static function getChatUsersList()
    {
        $ret = Db::name('user')
            ->field('id, username, nickname, avatar, bio')
            ->where('status', 'normal')
            ->select();
        return array_map(function($v) {
            $v['type'] = '';
            return $v;
        }, $ret);
    }

    /**
     * 根据用户查询其已经加入的群组id 以数组展示
     * @param  int $uid
     * @return array
     */
    public static function getRoomIdByUser($uid)
    {
        $g = self::field('room_id')
            ->where('uid', $uid)
            ->select();
        // 再取出特殊群组 比如所有群聊
        $s_group = Room::field('id')
            ->where('status', "1")
            ->where('id', 'LT', 100)
            ->select();
        return array_merge(array_column($s_group, 'id'), array_column($g, 'room_id'));
    }
}
