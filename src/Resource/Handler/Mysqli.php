<?php
namespace Ice\Resource\Handler;
class Mysqli extends Abs {

    public function query($sql) {
        if (!is_object($this->conn)) {
            return FALSE;
        }

        // 异常SQL过滤
        if ($this->isUnexpectSql($sql)) {
            return FALSE;
        }

        return $this->conn->query($sql);

    }

    public function __call($method, $arguments) {
        if (!is_object($this->conn)) {
            return FALSE;
        }
        return call_user_func_array(array($this->conn, $method), $arguments);
    }

    public function __get($name) {
        if (!is_object($this->conn)) {
            return null;
        }
        return $this->conn->$name;
    }

    /**
     * isUnexpectSql
     * 异常SQL过滤
     * @param mixed $sql
     * @access protected
     * @return void
     */
    protected function isUnexpectSql($sql) {
        // 超过2M的Sql拒绝执行
        if (strlen($sql) >= 2097152) {
            \F_Ice::$ins->mainApp->logger_common->warn(array(
                'sql'   => substr($sql, 0, 5000),
                'limit' => '2M',
            ), \F_ECode::MYSQL_QUERY_SQL_TOO_LONG);
            return TRUE;
        }

        // 超过50K的Sql报警
        if (strlen($sql) >= 51200) {
            \F_Ice::$ins->mainApp->logger_common->warn(array(
                'sql'   => substr($sql, 0, 5000),
                'limit' => '50K',
            ), \F_ECode::MYSQL_QUERY_SQL_TOO_LONG);
        }

        // update/delete无where条件不允许执行
        if (preg_match(';^\s*(update|delete)\b(?!.*\bwhere\b);ims', $sql)) {
            \F_Ice::$ins->mainApp->logger_common->warn(array(
                'sql'   => $sql,
            ), \F_ECode::MYSQL_QUERY_WRITE_NO_WHERE);
            return TRUE;
        }

        return FALSE;
    }
}
