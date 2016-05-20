<?php
namespace ice\home\Action\Homepage;
class Index extends \ice\home\Lib\Base\Action {
    public function realExecute() {
        return array(
            'body_content' => $this->markdown->render('/homepage/about'),
        );
    }
}