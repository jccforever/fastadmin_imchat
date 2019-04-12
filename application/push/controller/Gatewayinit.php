<?php
// linux or mac
namespace app\push\controller;

use Workerman\Worker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;

class Gatewayinit 
{
    /**
     * 构造函数
     */
    public function __construct ()
    {
        /*********register**********/
        // register 服务必须是text协议
        $register = new Register('text://0.0.0.0:1236');

        /*********bussinessWorker**********/
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = 'SendBusinessWorker';
        // bussinessWorker进程数量
        $worker->count = 4;
        // 服务注册地址
        $worker->registerAddress = '127.0.0.1:1236';
        //设置处理业务的类,此处制定Events的命名空间
        $worker->eventHandler = 'app\push\controller\Events';

        /***************gateway*****************/
        // gateway 进程
        $gateway = new Gateway("Websocket://0.0.0.0:7887");
        // 设置名称
        $gateway->name = 'SendGateway';
        // 设置进程数
        $gateway->count = 4;
        // 分布式部署时请设置成内网ip（非127.0.0.1）
        $gateway->lanIp = '127.0.0.1';
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = 2900;
        // 心跳间隔
        $gateway->pingInterval = 10;
        // 心跳数据
        $gateway->pingData = '{"type":"pong"}';
        // 服务注册地址
        $gateway->registerAddress = '127.0.0.1:1236';

        /*
         // 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
         $gateway->onConnect = function($connection)
         {
         $connection->onWebSocketConnect = function($connection , $http_header)
         {
         // 可以在这里判断连接来源是否合法，不合法就关掉连接
         // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
         if($_SERVER['HTTP_ORIGIN'] != 'http://chat.workerman.net')
         {
         $connection->close();
         }
         // onWebSocketConnect 里面$_GET $_SERVER是可用的
         // var_dump($_GET, $_SERVER);
         };
         };
         */

        // 如果不是在根目录启动，则运行runAll方法
        if (!defined('GLOBAL_START')) {
            Worker::runAll();
        }
    }
}