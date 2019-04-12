<?php

namespace app\admin\controller\imchat;

use app\common\controller\Backend;
use \app\common\model\imchat\RoomUser;

/**
 * 聊天室房间管理
 *
 * @icon fa fa-comment
 */
class Room extends Backend
{
    
    /**
     * Room模型对象
     * @var \app\common\model\imchat\Room
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\imchat\Room;
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
    public function member_assign ($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if ($params) {

                try {
                    $us = array_unique(explode(',', $params['rules']));
                    $us = array_filter(array_map(function($a) use ($ids) {
                        if (strpos($a, 'group_') === false)
                            return [
                                "room_id"=> $ids,
                                "uid"=> (int)$a
                            ];
                    }, $us));
                    $RoomUser = new RoomUser;
                    // 先删除所有
                    $RoomUser->where("room_id", $ids)->delete();
                    // 插入
                    $RoomUser->saveAll($us);
                    $this->success();
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $this->view->assign("nodeList", RoomUser::getAllUserToSelect($ids));
        return $this->view->fetch();
    }
}
