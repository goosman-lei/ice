<?php
namespace ice\home\Lib\Base;
class Action extends \FW_Action {
    public function execute() {
        $this->init();

        return $this->realExecute();
    }

    protected function init() {
        $navigationService = $this->ice->workApp->proxy_service->get('internal', 'Navigation');
        $retArr = $navigationService->getAllNodes($this->request->class, $this->request->action);

        if ($retArr['code'] == 0) {
            $this->response->addTplData(array(
                'rootNavs'    => $retArr['data']['rootNavs'],
                'currNavRoot' => $retArr['data']['currNavRoot'],
            ));
        } else {
            $this->response->addTplData(array(
                'rootNavs'    => array(),
                'currNavRoot' => array(),
            ));
        }
    }
}
