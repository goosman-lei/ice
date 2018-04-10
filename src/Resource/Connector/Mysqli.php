<?php
namespace Ice\Resource\Connector;
class Mysqli extends Abs {
    
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        if (!isset($nodeOptions['dbname'])) {
            $nodeOptions['dbname'] = '';
        }
        if (!isset($nodeOptions['warn_sql_length'])) {
            $nodeOptions['warn_sql_length'] = 2097152;
        }
        if (!isset($nodeOptions['fatal_sql_length'])) {
            $nodeOptions['fatal_sql_length'] = 51200;
        }
        if (!isset($nodeOptions['deny_empty_update_delete'])) {
            $nodeOptions['deny_empty_update_delete'] = TRUE;
        }
        return array($nodeConfig, $nodeOptions);
    }

    public static function getSn($nodeConfig, $nodeOptions) {
        return sprintf('%s:%s:%s:%s', $nodeConfig['host'], $nodeConfig['port'], $nodeOptions['dbname'], $nodeOptions['user']);
    }

    public static function getConn($nodeInfo) {
        $mysqli  = new \Mysqli();
        $options = $nodeInfo['options'];
        $config  = $nodeInfo['config'];

        if (isset($options['timeout'])) {
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, intval($options['timeout']));
        }

        // 连接
        @$mysqli->real_connect($config['host'], $options['user'], $options['passwd'], (string)$options['dbname'], $config['port']);
        if ($mysqli->connect_errno) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'host'   => $config['host'],
                'port'   => $config['port'],
                'dbname' => $options['dbname'],
                'user'   => $options['user'],
                'errno'  => $mysqli->connect_errno,
                'error'  => $mysqli->connect_error,
            ), \F_ECode::MYSQL_CONN_ERROR);
            return FALSE;
        }

        if (isset($options['charset'])) {
            $mysqli->set_charset($options['charset']);
            if ($mysqli->errno) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'host'    => $config['host'],
                    'port'    => $config['port'],
                    'dbname'  => $options['dbname'],
                    'user'    => $options['user'],
                    'charset' => $options['charset'],
                    'errno'   => $mysqli->errno,
                    'error'   => $mysqli->error,
                ), \F_ECode::MYSQL_SET_CHARSET_FAILED);
            }
        }

        if (isset($options['collation'])) {
            $mysqli->query('SET collation_connection = ' . $options['collation']);
            if ($mysqli->errno) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'host'      => $config['host'],
                    'port'      => $config['port'],
                    'dbname'    => $options['dbname'],
                    'user'      => $options['user'],
                    'collation' => $options['collation'],
                    'errno'     => $mysqli->errno,
                    'error'     => $mysqli->error,
                ), \F_ECode::MYSQL_SET_COLLATION_FAILED);
            }
        }

        return $mysqli;
    }
}
