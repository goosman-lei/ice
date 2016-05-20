<?php
namespace ice\home\Model;
class UserIssue extends \DB_Query {
    protected $tableName = 'user_issue';
    protected $mapping   = array(
        'id'      => 'i',
        'contact' => 's',
        'title'   => 's',
        'content' => 's',
    );
    protected $dbResource = 'ice_home';

}
