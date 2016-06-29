<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Demo;
/**
 * Message 
 * 使用方法如下:
# 启动生产者. 模拟请求入口
php -ddisplay_errors=on src/daemon/cli.php --class=demo --action=message --runas=producer

# 启动slave消费者, 模拟本机房slave任务补充消费者
php -ddisplay_errors=on src/daemon/cli.php --class=demo --action=message --runas=slave_consumer

# 启动master消费者, 模拟本机房master任务补充消费者. (生产环境部署在主机房)
php -ddisplay_errors=on src/daemon/cli.php --class=demo --action=message --runas=master_consumer

 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class Message extends \FD_Daemon {
    public function slave_handler($msg) {
        $msgBody = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message');
        echo 'slave daemon: ' . $msgBody . chr(10);
        $proxy->call('slave', $msgBody);
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
    }
    public function master_handler($msg) {
        $msgBody = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message');
        echo 'master daemon: ' . $msgBody . chr(10);
        $proxy->call('master', $msgBody);
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
    }
    public function execute() {
        $runas = $this->request->getOption('runas', 'producer');

        if ($runas == 'producer') {
            // 多机房消息主入口
            $proxy = $this->ice->workApp->proxy_service->get('internal', 'Say');
            $i = 0;
            while (TRUE) {
                $proxy->message('Daemon');
                echo "produce " . ($i ++) . chr(10);
                echo chr(10);
                sleep(1);
            }
        }

        if ($runas == 'slave_consumer') {
            // 多机房消费从业务补充入口
            $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
            $proxyQueue->consume('multi_server_room_slave_queue', array($this, 'slave_handler'));
            while (TRUE) {
                $proxyQueue->wait();
                echo chr(10);
            }
        }

        if ($runas == 'master_consumer') {
            // 多机房消费主业务补充入口
            $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
            $proxyQueue->consume('multi_server_room_master_queue', array($this, 'master_handler'));
            while (TRUE) {
                $proxyQueue->wait();
                echo chr(10);
            }
        }
    }
}
