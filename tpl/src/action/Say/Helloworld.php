<?php
namespace ${PROJECT_NAMESPACE}\Action\Say;
class Helloworld extends \FW_Action {
    public function execute() {
        $user = new User();
        $uinfo = $user->getRow(array('id', '5012470'), 'id, name, location');
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
                }
            }
        }')->filter(array(
            'code' => 0,
            'data' => array(
                'uid'   => 5012470,
                'uname' => 'goosman-lei',
                'service' => $client->hello('Jack'),
                'user'    => $uinfo,
            ),
        ));
    }
}

class User extends \DB_Query {
    protected $tableName = 'kk_user';
    protected $mapping   = array(
        'id'     => 'i',
        'passwd' => 's',
        'name'   => 's',
        'avatar' => 's',
        'wid'    => 's',
    );
    protected $dbResource = 'demo';
}
