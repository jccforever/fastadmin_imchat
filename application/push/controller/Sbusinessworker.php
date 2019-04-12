<?php 
// only win
namespace app\push\controller;

use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

class Sbusinessworker{
    public function __construct(){
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

        // 如果不是在根目录启动，则运行runAll方法
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }
}
