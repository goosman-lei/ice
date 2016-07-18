<?php
namespace ${PROJECT_NAMESPACE}\Model;
/*
CREATE TABLE IF NOT EXISTS user (
   id INT NOT NULL PRIMARY KEY,
   name VARCHAR(64) NOT NULL DEFAULT '',
   passwd VARCHAR(64) NOT NULL DEFAULT '',
   location VARCHAR(64) NOT NULL DEFAULT ''
) ENGINE Innodb DEFAULT CHARACTER SET UTF8;
 */
class User extends \Ice_DB_Query {
    protected $tableName = 'user';
    protected $mapping   = array(
        'id'       => 'i',
        'name'     => 's',
        'passwd'   => 's',
        'location' => 's',
    );
    protected $dbResource = 'demo';
}
