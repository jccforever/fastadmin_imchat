<?php

namespace addons\imchat;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Imchat extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'   => 'imchat',
                'title'  => '聊天系统管理',
                'ismenu' => 1,
                'icon'   => 'fa fa-comment',
                'sublist'=> [
                    [
                        'name'    => 'imchat/room',
                        'title'   => '群组管理',
                        'icon'    => 'fa fa-wpforms',
                        'remark' => '用户聊天系统，适用于前台用户的聊天交流，常规群组人员需要后台自行分配，请勿将常规群组id修改到100以下。<a href="/addons/imchat/index/" target="_black">测试链接: http://yourdomain/addons/imchat/index/</a>',
                        'sublist' => [
                            ['name' => 'imchat/room/index', 'title' => '查看'],
                            ['name' => 'imchat/room/add', 'title' => '添加'],
                            ['name' => 'imchat/room/edit', 'title' => '修改'],
                            ['name' => 'imchat/room/del', 'title' => '删除']
                        ]
                    ],
                    [
                        'name'    => 'imchat/imchatmessage',
                        'title'   => '内容管理',
                        'icon'    => 'fa fa-comment-o',
                        'remark' => '群组消息是否已读不存在意义',
                        'sublist' => [
                            ['name' => 'imchat/imchatmessage/index', 'title' => '查看'],
                            ['name' => 'imchat/imchatmessage/del', 'title' => '删除']
                        ]
                    ],
                ],
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('imchat');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('imchat');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('imchat');
        return true;
    }

    /**
     * 实现钩子方法
     * @return mixed
     */
    public function testhook($param)
    {
        // 调用钩子时候的参数信息
        print_r($param);
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        print_r($this->getConfig());
        // 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
        //return $this->fetch('view/info');
    }

}
