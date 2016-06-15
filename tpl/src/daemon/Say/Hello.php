<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Say;
class Hello extends \FD_Daemon {
    public function slave_handler($msg) {
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
        $msg = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message', 'Say');
        echo 'slave msg: ' . $msg . chr(10);
        $proxy->call('slave', $msg);
    }
    public function master_handler($msg) {
        $msg->get('channel')->basic_ack($msg->get('delivery_tag'));
        $msg = $msg->getBody();
        $proxy = $this->ice->workApp->proxy_service->get('message', 'Say');
        echo 'master msg: ' . $msg . chr(10);
        $proxy->call('master', $msg);
    }
    public function execute() {
        // 多机房消息主入口
        if (FALSE) {
            $client = $this->ice->workApp->proxy_service->get('internal', 'Say');
            $client->hello('Daemon');
        }
        // 多机房消费从业务补充入口
        if (FALSE) {
            $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
            $proxyQueue->consume('multi_server_room_slave_queue', array($this, 'slave_handler'));
            $proxyQueue->wait();
        }
        // 多机房消费主业务补充入口
        if (FALSE) {
            $proxyQueue = \F_Ice::$ins->workApp->proxy_resource->get('rabbitmq://demo');
            $proxyQueue->consume('multi_server_room_master_queue', array($this, 'master_handler'));
            $proxyQueue->wait();
        }
        exit;
            $code = '(map){
    code(int);
    data(map){
        is_new_tag(str);
        picSize(int);
        webp(str);
        hide_chat_emoticon(str);
        emoticon_shops(arr){
            *(map) @"ext.emoticon_1"|@"ext.emoticon_2"{
                enable(str) range;
                new_emoticon(int:0)
            }{
                new_emoticon(int:3)
            }strip:new_emoticon
        };
        host_white(arr){
            *(str)
        }
    }
}';

        $filter = $this->ice->workApp->proxy_filter->get($code);
        $filterdData = $filter->filter(array(
            'code' => '0',
            'data' => array(
                'what' => 'hal',
                'is_new_tag' => 'no',
                'picSize' => '102',
                'webp' => 'no',
                'hide_chat_emoticon' => 'yes',
                'emoticon_shops' => 'yes',
                'emoticon_shops' => array(
                    array(
                        'enable' => 'no',
                        'new_emoticon' => '4',
                    ),
                    array(
                        'enable' => 'yes',
                        'new_emoticon' => '5',
                    ),
                ),
                'host_white' => array(
                    'oneniceapp.com',
                    'niceprivate.com',
                ),
            ),
        ));
        echo $code . chr(10) . chr(10);
        echo json_encode($filterdData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . chr(10) . chr(10);

        $client = $this->ice->workApp->proxy_service->get('internal', 'Say');
        $this->output($client->hello('Daemon'));
        $this->output(json_encode($this->request->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->output(json_encode($this->request->argv, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
