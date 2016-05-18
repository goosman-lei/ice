<?php
namespace ice\home\Model;
class Navigation extends \DB_Query {
    protected $tableName = 'navigation';
    protected $mapping   = array(
        'id'   => 'i',
        'pid'  => 'i',
        'name' => 's',
        'url'  => 's',
    );
    protected $dbResource = 'ice_home';
}
