<?php
namespace ice\home\Lib\Base;
class Action extends \FW_Action {
    protected $inputFilter;
    protected $outputFilter;

    protected $markdown;

    protected $input;

    public function execute() {
        $this->init();

        return $this->realExecute();
    }

    protected function init() {
        $this->markdown = new \H_Markdown();

        $get  = $this->request->getQueries();
        $post = $this->request->getPosts();
        $file = $this->request->getFiles();

        $input = array_merge($get, $post, $file);

        if (isset($this->inputFilter)) {
            $input = $this->ice->workApp->proxy_filter->get($this->inputFilter, TRUE)->filter($input);
            if ($input === FALSE) {
                return $this->error(\H_ECode::COMMON_INPUT_ERROR);
            } else {
                $this->input = $input;
            }
        } else {
            $this->input = $input;
        }
    }

    protected function succ($data = null) {
        $data = isset($data) ? $data : new \U_Map;
        if (isset($this->outputFilter)) {
            $data = $this->ice->workApp->proxy_filter->get($this->outputFilter)->filter($data);
        }

        return array(
            'code' => 0,
            'data' => $data,
        );
    }

    protected function error($code, $data = null) {
        $data = isset($data) ? $data : new \U_Map;
        return $this->response->error($code, $data);
    }
}
