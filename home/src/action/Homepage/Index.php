<?php
namespace ice\home\Action\Homepage;
class Index extends \H_Action {
    public function realExecute() {
        return array(
            'body_content' => $this->markdown->render('/homepage/about'),
        );
    }
}