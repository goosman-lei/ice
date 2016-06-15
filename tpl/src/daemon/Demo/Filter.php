<?php
namespace ${PROJECT_NAMESPACE}\Daemon\Demo;
class Filter extends \FD_Daemon {
    public function execute() {
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
    }
}
