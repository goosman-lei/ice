<?php
class Filter {
    /*
$code = '(map){
    code(int);
    data(map){
        is_new_tag(str) enum:yes,no;
        picSize(int);
        webp(str) enum:yes,no;
        hide_chat_emoticon(str) enum:yes,no;
        emoticon_shops(map){
            *(map){
                enable(str) enum:yes,no;
                new_emoticon(int:0);
            }
        };
        host_white(arr){
            *(str)
        }
    };
}'
$data = array(
    'code' => 0,
    'data' => array(
        'is_new_tag' => 'yes',
        'picSize'    => 1,
        'webp'       => 'yes',
        'host_white' => array(
            'oneniceapp.com',
            'niceprivate.com',
        ),
        'hide_chat_emoticon' => 'no',
        'emoticon_shops'     => array(
            array(
                'enable'       => 'yes',
                'new_emoticon' => 6,
            ),
            array(
                'enable'       => 'no',
                'new_emoticon' => 6,
            ),
        ),
    ),
);
    */
    protected $mode = 'strict';
    public function filter(&$data) {
        try {
            $expectData = $this->defaultMap;
            $this->type_map($data);
            if (is_array($data)) {
                $expectData['code'] = $this->defaultInt;
                $this->type_int(@$data['code']);

                $expectData['data'] = $this->defaultMap;
                $this->type_map(@$data['data']);
                if (is_array(@$data['data'])) {
                    $expectData['data']['is_new_tag'] = $this->defaultStr;
                    $this->type_str(@$data['data']['is_new_tag']);
                    $this->filter_enum(@$data['data']['is_new_tag'], 'yes', 'no');

                    $expectData['data']['picSize'] = $this->defaultInt;
                    $this->type_int(@$data['data']['picSize']);

                    $expectData['data']['webp'] = $this->defaultStr;
                    $this->type_str(@$data['data']['webp']);
                    $this->filter_enum(@$data['data']['webp'], 'yes', 'no');

                    $expectData['data']['hide_chat_emoticon'] = $this->defaultStr;
                    $this->type_str(@$data['data']['hide_chat_emoticon']);
                    $this->filter_enum(@$data['data']['hide_chat_emoticon'], 'yes', 'no');

                    $expectData['data']['emoticon_shops'] = $this->defaultArr;
                    $this->type_arr(@$data['data']['emoticon_shops']);
                    if (is_array(@$data['data']['emoticon_shops'])) {
                        foreach (@$data['data']['emoticon_shops'] as $k => $v) {

                            $expectData['data']['emoticon_shops'][$k] = $this->defaultMap;
                            $this->type_map(@$data['data']['emoticon_shops'][$k]);
                            if (is_array(@$data['data']['emoticon_shops'][$k])) {

                                $expectData['data']['emoticon_shops'][$k]['enable'] = $this->defaultStr;
                                $this->type_str(@$data['data']['emoticon_shops'][$k]['enable']);
                                $this->filter_enum(@$data['data']['emoticon_shops'][$k]['enable'], 'yes', 'no');

                                $expectData['data']['emoticon_shops'][$k]['new_emoticon'] = $this->defaultInt;
                                $this->type_int(@$data['data']['emoticon_shops'][$k]['new_emoticon'], 0);
                            }
                        }
                    }

                    $expectData['data']['host_white'] = $this->defaultArr;
                    $this->type_arr(@$data['data']['host_white']);
                    if (is_array(@$data['data']['host_white'])) {
                        foreach (@$data['data']['host_white'] as $key => $value) {
                            $expectData['data']['host_white'][$key] = $this->defaultStr;
                            $this->type_str(@$data['data']['host_white'][$key]);
                        }
                    }
                }
            }

            foreach ($expectData as $k => $v) {
            }
        } catch (FilterException $e) {
            return FALSE;
        }
    }
}
