<?php
/* 类名映射 */
class_alias('\\Ice\\Frame\\Config',             'F_Config');
class_alias('\\Ice\\Frame\\Ice',                'F_Ice');
class_alias('\\Ice\\Frame\\App',                'F_App');
class_alias('\\Ice\\Frame\\Logger',             'F_Logger');
class_alias('\\Ice\\Frame\\Error\\Code',        'F_ECode');

class_alias('\\Ice\\Frame\\Web\\Action',        'FW_Action');
class_alias('\\Ice\\Frame\\Web\\UnitTest',      'FW_UT');

class_alias('\\Ice\\Frame\\Service\\Service',   'FS_Service');

class_alias('\\Ice\\Frame\\Daemon\\Daemon',     'FD_Daemon');

class_alias('\\Ice\\DB\\Query',                 'Ice_DB_Query');
class_alias('\\Ice\\DB\\ShardQuery',            'Ice_DB_SQuery');
class_alias('\\Ice\\DB\\BaseModel',             'Ice_DB_Model');

class_alias('\\Ice\\Resource\\Helper\\Redis',   'Helper_Redis');
class_alias('\\Ice\\Resource\\Helper\\Rabbitmq','Helper_Rabbitmq');

class_alias('\\Ice\\Message\\Factory',          'MSG_Factory');
class_alias('\\Ice\\Message\\Abs',              'MSG_Abs');

class_alias('\\Ice\\Util\\Time',                'U_Time');
class_alias('\\Ice\\Util\\Env',                 'U_Env');
class_alias('\\Ice\\Util\\Ip',                  'U_Ip');
class_alias('\\Ice\\Util\\I18N',                'U_I18N');
class_alias('\\Ice\\Util\\Path',                'U_Path');
class_alias('\\Ice\\Util\\Rusage',              'U_Rusage');

class_alias('\\Ice\\Util\\DString',             'U_String');
class_alias('\\Ice\\Util\\DMoney',             'U_Money');
class_alias('\\Ice\\Util\\DMap',                'U_Map');
class_alias('\\Ice\\Util\\DStub',               'U_Stub');
class_alias('\\Ice\\Util\\DArray',              'U_Array');
class_alias('\\Ice\\Util\\DNumber',             'U_Number');
class_alias('\\Ice\\Util\\DLRU',                'U_LRU');
