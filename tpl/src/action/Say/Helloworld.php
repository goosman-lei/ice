<?php
namespace ${PROJECT_NAMESPACE}\Action\Say;
class Helloworld extends \FW_Action {
    public function execute() {
        $userModel = $this->ice->workApp->getModel('user');
        $uinfo = $userModel->getRow(array('id', '1'), 'id, name, location');
        $client = $this->ice->mainApp->proxy_service->get('demo-local', 'Say');
        return $this->ice->mainApp->proxy_filter->get('(map){
            code(int);
            data(map){
                uid(int);
                uname(str);
                service(map){
                    code(int);
                    data(str);
                };
                user(map){
                    id(int);
                    name(str);
                    location(str);
                };
                is_local(str)
            }
        }')->filter(array(
            'code' => 0,
            'data' => array(
                'uid'   => 5012470,
                'uname' => 'goosman-lei',
                'service' => $client->hello('Jack'),
                'user'    => $uinfo,
                'is_local' => $this->ice->runner->feature->isEnable('is_local_access') ? 'yes' : 'no',
            ),
        ));
    }
}
