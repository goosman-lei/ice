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
        if (strlen($sql) >= $this->nodeOptions['fatal_sql_length']) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'sql'   => substr($sql, 0, 5000),
                'limit' => $this->nodeOptions['warn_sql_length'],
            ), \F_ECode::MYSQL_QUERY_SQL_TOO_LONG);
            return TRUE;
        }

        // 超过50K的Sql报警
        if (strlen($sql) >= $this->nodeOptions['warn_sql_length']) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'sql'   => substr($sql, 0, 5000),
                'limit' => $this->nodeOptions['warn_sql_length'],
            ), \F_ECode::MYSQL_QUERY_SQL_TOO_LONG);
        }

        // update/delete无where条件不允许执行
        if ($this->nodeOptions['deny_empty_update_delete'] && preg_match(';^\s*(update|delete)\b(?!.*\bwhere\b);ims', $sql)) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'sql'   => substr($sql, 0, 5000),
            ), \F_ECode::MYSQL_QUERY_WRITE_NO_WHERE);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * isDuplicated
     * 检查当前是否由于唯一键冲突失败
     * @access protected
     * @return void
     */
    public function isDuplicated() {
        return $this->conn->errno === 1062;
    }
}
