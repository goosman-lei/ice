<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Demo;
class Message extends \FD_Daemon {
    public function slave_handler($msg) {
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
        $msg = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message');
        echo 'slave daemon: ' . chr(10);
        var_dump($proxy->call('slave', $msg));
        echo chr(10);
    }
    public function master_handler($msg) {
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
        $msg = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message');
        echo 'master daemon: ' . chr(10);
        var_dump($proxy->call('master', $msg));
        echo chr(10);
    }
    public function execute() {
        // 多机房消息主入口
        $proxy = $this->ice->workApp->proxy_service->get('internal', 'Say');
        echo 'main: ' . chr(10);
        var_dump($proxy->hello('Daemon'));

        // 模拟等待消息入队列成功
        sleep(2);

        // 多机房消费从业务补充入口
        $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
        $proxyQueue->consume('multi_server_room_slave_queue', array($this, 'slave_handler'));
        $proxyQueue->wait();

        // 多机房消费主业务补充入口
        $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
        $proxyQueue->consume('multi_server_room_master_queue', array($this, 'master_handler'));
        $proxyQueue->wait();
    }
}
