<?php
namespace ${PROJECT_NAMESPACE}\Model;
class User extends \DB_Query {
    protected $tableName = 'user';
    protected $mapping   = array(
        'id'       => 'i',
        'name'     => 's',
        'passwd'   => 's',
        'location' => 's',
    );
    protected $dbResource = 'demo';
}
