<?php
namespace Ice\DB;
class Query {
    const RS_NONE  = 0;
    const RS_ARRAY = 1;
    const RS_NUM   = 2;

    const DEFAULT_QUERY_LIMIT = 10000;

    protected $tableName;
    protected $mapping = array();
    protected $dbResource;

    protected $affectedNum = 0;
    protected $lastId = 0;
    const MASTER = 'master';
    const SLAVE  = 'slave';
    const AUTO  = 'auto';
    private static $cluster = 'auto';

    /**
     * affectedNum
     * 获取上条SQL受影响行数
     * @access protected
     * @return void
     */
    public function affectedNum() {
        return $this->affectedNum;
    }

    /**
     * lastId 
     * 获取上条SQL写入产生的自增ID
     * @access public
     * @return void
     */
    public function lastId() {
        return $this->lastId;
    }
    
    public static function switchCluster($cluster) {
        if (!in_array($cluster, array(self::MASTER, self::SLAVE, self::AUTO))) {
            return false;
        }
        $preCluster = self::$cluster;
        self::$cluster = $cluster;
        return $preCluster;
    }

    public function query($sql, $onReturn = self::RS_ARRAY) {
        if (self::$cluster == self::MASTER) {
            $cluster = self::MASTER;
        } else if (self::$cluster == self::SLAVE) {
            $cluster = self::SLAVE;
        } else {
            $cluster = $this->isMasterSql($sql) ? self::MASTER : self::SLAVE;
        }
        $dsn     = 'mysqli://' . $this->dbResource . '/' . $cluster;
        $handler = \F_Ice::$ins->workApp->proxy_resource->get($dsn);
        if (!$handler || ($handler instanceof \Ice\Util\DStub)) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'sql' => substr($sql, 0, 5000),
                'dsn' => $dsn,
            ), \F_ECode::QUERY_GET_HANDLER_FAILED);
            return FALSE;
        }

        $rs = $handler->query($sql);
        if ($handler->errno && !$handler->isDuplicated()) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'sql'   => substr($sql, 0, 5000),
                'errno' => $handler->errno,
                'error' => $handler->error,
            ), \F_ECode::QUERY_QUERY_FAILED);
            return FALSE;
        }

        if ($onReturn !== self::RS_NONE && is_object($rs) && ($rs instanceof \Mysqli_Result)) {
            switch ($onReturn) {
                case self::RS_NUM:
                    $rows = array();
                    while ($row = $rs->fetch_row()) {
                        $rows[] = $row;
                    }
                    break;
                case self::RS_ARRAY:
                default:
                    $rows = array();
                    while ($row = $rs->fetch_assoc()) {
                        $rows[] = $row;
                    }
                    break;
            }
            return is_array($rows) ? $rows : FALSE;
        }

        $this->affectedNum = $handler->affected_rows;
        $this->lastId = $handler->insert_id;
        return $rs === FALSE ? FALSE : TRUE;
    }

    /**
     * execute
     * 执行一条SQL, 但不关注结果集
     * @param string $sql
     * @access public
     * @return void
     */
    public function execute($sql) {
        return $this->query($sql, self::RS_NONE);
    }


    /**
     * isMasterSql
     * 检查给定SQL是否需要切换到master集群
     * @param mixed $sql
     * @access protected
     * @return bool
     */
    protected function isMasterSql($sql) {
        return preg_match(';^\s*(?:SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD\s+DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK|START\s+TRANSACTION|COMMIT|ROLLBACK)\s+|/\*\s*MASTER\s*\*/;i', $sql);
    }

    /**
     * getRow
     * 获取符合条件的一行记录
     * @param mixed $where 参见buildWhere()
     * @param mixed $cols 参见buildSelect()
     * @param mixed $orderBy 参见buildOrderBy()
     * @param mixed $join 参见buildJoin()
     * @param mixed $groupBy 参见buildGroupBy()
     * @param mixed $having 参见buildHaving()
     * @param mixed $tableOptions 参见buildFrom()
     * @access public
     * @return 成功且有记录 array
     * @return 成功且无记录 NULL
     * @return 失败 FALSE
     */
    public function getRow($where = array(), $cols = '*', $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE) {
        $rows = $this->getRows($where, $cols, 1, 0, $orderBy, $join, $groupBy, $having, $tableOptions, $selectOptions);
        if (!is_array($rows)) {
            return FALSE;
        }
        return count($rows) === 1 ? $rows[0] : array();
    }

    /**
     * getRows
     * 获取多行记录, example:
     * @param mixed $where 参见buildWhere()
     * @param mixed $cols 参见buildSelect()
     * @param mixed $limit 参见buildLimit()
     * @param mixed $offset 参见buildLimit()
     * @param mixed $orderBy 参见buildOrderBy()
     * @param mixed $join 参见buildJoin()
     * @param mixed $groupBy 参见buildGroupBy()
     * @param mixed $having 参见buildHaving()
     * @param mixed $tableOptions 参见buildFrom()
     * @access public
     * @return mixed | 成功 array 失败 false
     */
    public function getRows($where = array(), $cols = '*', $limit = FALSE, $offset = 0, $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE) {
        $selectClause  = $this->buildSelect($cols, $selectOptions);
        $fromClause    = $this->buildFrom($where, $tableOptions);
        $joinClause    = $this->buildJoin($join);
        $whereClause   = $this->buildWhere($where);
        $groupByClause = $this->buildGroupBy($groupBy);
        $havingClause  = $this->buildHaving($having);
        $orderByClause = $this->buildOrderBy($orderBy);
        $limitClause   = $this->buildLimit($limit, $offset);
        $sql = $selectClause . $fromClause . $joinClause . $whereClause . $groupByClause . $havingClause . $orderByClause . $limitClause;
        return $this->query($sql);
    }

    /**
     * count
     * 获取符合条件的记录数
     * @param mixed $where 参见buildWhere()
     * @param mixed $join 参见buildJoin()
     * @param string $countField
     * @param mixed $tableOptions 参见buildFrom()
     * @access public
     * @return 失败 FALSE
     * @return 成功 int
     */
    public function count($where = array(), $join = FALSE, $countField = '*', $tableOptions = FALSE) {
        $row = $this->getRow($where, 'count(' . $countField . ') AS count', FALSE, $join, FALSE, FALSE, $tableOptions);
        if (!is_array($row) || empty($row)) {
            return FALSE;
        }
        return intval($row['count']);
    }

    /**
     * update
     * 更新符合条件的数据
     * @param mixed $setValues 参见buildSet()
     * @param mixed $where 参见buildWhere()
     * @param mixed $orderBy 参见buildOrderBy()
     * @param mixed $limit 参见buildLimit()
     * @access public
     * @return 失败 FALSE
     * @return 成功 影响行数
     */
    public function update($setValues, $where = array(), $orderBy = FALSE, $limit = FALSE) {
        $setClause     = $this->buildSet($setValues);
        if (empty($setClause)) {
            return FALSE;
        }

        $whereClause   = $this->buildWhere($where);
        $orderByClause = $this->buildOrderBy($orderBy);
        $limitClause   = $this->buildLimit($limit, 0);

        $sql = 'UPDATE ' . $this->getTableName() . $setClause . $whereClause . $orderByClause . $limitClause;

        $ret = $this->execute($sql);
        if ($ret === FALSE) {
            return FALSE;
        }

        return intval($this->affectedNum());
    }

    /**
     * insert
     * 插入数据
     * @param mixed $setValues 参见buildValues()
     * @param mixed $onDup 参见buildOnDup()
     * @access public
     * @return 失败 FALSE
     * @return 成功且无onDup last insert id
     * @return 成功且有onDup 影响行数
     */
    public function insert($setValues, $onDup = FALSE) {
        $valClause   = $this->buildValues($setValues);
        if (!$valClause) {
            return FALSE;
        }

        $valNameExpr = $this->buildValueNameExpr($setValues);
        $onDupClause = $this->buildOnDup($setValues, $onDup);

        $sql = 'INSERT INTO ' . $this->getTableName() . '(' . $valNameExpr . ')' . $valClause . $onDupClause;

        $ret = $this->execute($sql);
        if ($ret === FALSE) {
            return FALSE;
        }

        return $onDup === FALSE
            ? (int)$this->lastId()
            : (int)$this->affectedNum();
    }

    /**
     * multiInsert
     * 插入数据
     * @param mixed $setValues 参见buildMultiValues()
     * @access public
     * @return 失败 FALSE
     * @return 成功 影响行数
     */
    public function multiInsert($setValues) {
        $valClause   = $this->buildMultiValues($setValues);
        if (!$valClause) {
            return FALSE;
        }

        $valNameExpr = $this->buildValueNameExpr($setValues[0]);

        $sql = 'INSERT INTO ' . $this->getTableName() . '(' . $valNameExpr . ')' . $valClause;

        $ret = $this->execute($sql);
        if ($ret === FALSE) {
            return FALSE;
        }

        return $this->affectedNum();
    }

    /**
     * replace
     * 插入数据
     * @param mixed $setValues 参见buildValues()
     * @access public
     * @return 失败 FALSE
     * @return 成功 影响行数
     */
    public function replace($setValues) {
        $valClause   = $this->buildValues($setValues);
        if (!$valClause) {
            return FALSE;
        }

        $valNameExpr = $this->buildValueNameExpr($setValues);

        $sql = 'REPLACE INTO ' . $this->getTableName() . '(' . $valNameExpr . ')' . $valClause;

        $ret = $this->execute($sql);
        if ($ret === FALSE) {
            return FALSE;
        }

        return (int)$this->affectedNum();
    }

    /**
     * delete
     * 删除符合条件的数据
     * @param mixed $where 参见buildWhere()
     * @param mixed $orderBy 参见buildOrderBy()
     * @param mixed $limit 参见buildLimit()
     * @access public
     * @return 失败 FALSE
     * @return 成功 int 影响行数
     */
    public function delete($where = array(), $orderBy = FALSE, $limit = FALSE) {
        $whereClause   = $this->buildWhere($where);
        $orderByClause = $this->buildOrderBy($orderBy);
        $limitClause   = $this->buildLimit($limit, 0);

        $sql = 'DELETE FROM ' . $this->getTableName() . $whereClause . $orderByClause . $limitClause;

        $ret = $this->execute($sql);
        if ($ret === FALSE) {
            return FALSE;
        }

        return $this->affectedNum();
    }

    /**
     * buildSelect 
     * 构造SELECT子句
     * @param mixed $cols 
          '*' | FALSE
          'field1, field2 as alias'
          array('field1', 'field2', '`field3`' AS alias')
          仅数组方式传递, 且单独指定field时, 对field应用``符号
     * @param array $modifiers 
          [ALL | DISTINCT | DISTINCTROW ]
          [HIGH_PRIORITY]
          [MAX_STATEMENT_TIME = N]
          [STRAIGHT_JOIN]
          [SQL_SMALL_RESULT]
          [SQL_BIG_RESULT]
          [SQL_BUFFER_RESULT]
          [SQL_CACHE | SQL_NO_CACHE]
          [SQL_CALC_FOUND_ROWS]
     * @access protected
     * @return void
     */
    protected function buildSelect($cols, $modifiers = array()) {
        if (is_array($cols)) {
            $retCols = array();
            foreach ($cols as $col) {
                $retCols[] = $this->escapeFieldName($col);
            }
            $colsStr = implode(', ', $retCols);
        } else if ($cols === FALSE) {
            $colsStr = '*';
        } else {
            $colsStr = (string)$cols;
        }

        $modifierStr = '';
        if (!empty($modifiers)) {
            $modifierArr = array();
            foreach ($modifiers as $modifier) {
                $modifierArr[] = $modifier;
            }
            $modifierStr = implode(' ', $modifierArr) . ' ';
        }
        return 'SELECT ' . $modifierStr . ' ' . $colsStr;
    }

    /**
     * buildFrom 
     * 构造FROM子句
     * @param mixed $where (保留, 用于做分表策略使用)
     * @param mixed $tableOptions (index hint)
     * @access protected
     * @return void
     */
    protected function buildFrom($where, $tableOptions = FALSE) {
        // 暂不考虑分库分表
        $tableName = $this->getTableName($where);

        $fromClause = ' FROM ' . $tableName;
        if (!empty($tableOptions)) {
            if (isset($tableOptions['alias'])) {
                $fromClause .= ' AS ' . $tableOptions['alias'];
            }
            if (isset($tableOptions['use_index_for_join'])) {
                $fromClause .= ' USE INDEX FOR JOIN (' . $tableOptions['use_index_for_join'] . ')';
            }
            if (isset($tableOptions['use_index_for_orderby'])) {
                $fromClause .= ' USE INDEX FOR ORDER BY (' . $tableOptions['use_index_for_orderby'] . ')';
            }
            if (isset($tableOptions['use_index_for_groupby'])) {
                $fromClause .= ' USE INDEX FOR GROUP BY (' . $tableOptions['use_index_for_groupby'] . ')';
            }
            if (isset($tableOptions['ignore_index_for_join'])) {
                $fromClause .= ' IGNORE INDEX FOR JOIN (' . $tableOptions['ignore_index_for_join'] . ')';
            }
            if (isset($tableOptions['ignore_index_for_orderby'])) {
                $fromClause .= ' IGNORE INDEX FOR ORDER BY (' . $tableOptions['ignore_index_for_orderby'] . ')';
            }
            if (isset($tableOptions['ignore_index_for_groupby'])) {
                $fromClause .= ' IGNORE INDEX FOR GROUP BY (' . $tableOptions['ignore_index_for_groupby'] . ')';
            }
            if (isset($tableOptions['force_index_for_join'])) {
                $fromClause .= ' FORCE INDEX FOR JOIN (' . $tableOptions['force_index_for_join'] . ')';
            }
            if (isset($tableOptions['force_index_for_orderby'])) {
                $fromClause .= ' FORCE INDEX FOR ORDER BY (' . $tableOptions['force_index_for_orderby'] . ')';
            }
            if (isset($tableOptions['force_index_for_groupby'])) {
                $fromClause .= ' FORCE INDEX FOR GROUP BY (' . $tableOptions['force_index_for_groupby'] . ')';
            }
        }
        return $fromClause;
    }

    /**
     * buildJoin
     * 解析JOIN子句
     * @param mixed $joinArr
     *  examples:
     *      'kk_paster AS p1 ON p.id = p1.id'
     *      array(
     *          'kk_paster AS p1 ON p.id = p1.id',
     *          'left join kk_user AS u ON p.uid = u.id',
     *      )
     * @access protected
     * @return void
     */
    protected function buildJoin($joinArr) {
        if (empty($joinArr)) {
            return '';
        }
        if (is_array($joinArr)) {
            $joinStrArr = array();
            foreach ($joinArr as $joinStr) {
                if (!preg_match(';^\s*(?:(?:left|right)(?:\s+outer)?|inner)?\s*join\s+;i', $joinStr, $match)) {
                    $joinStr = 'JOIN ' . $joinStr;
                }
                $joinStrArr[] = $joinStr;
            }
            $joinArr = implode(' ', $joinStrArr);
        } else if (!preg_match(';^\s*(?:(?:left|right)(?:\s+outer)?|inner)?\s*join\s+;i', strval($joinArr))) {
            $joinArr = 'JOIN ' . $joinArr;
        }
        return ' ' . $joinArr;
    }

    /**
     * buildWhere 
     * 构造where子句
     * @param mixed $where 参照buildWhereExpr()
     * @access protected
     * @return void
     */
    protected function buildWhere($where) {
        if (is_array($where)) {
            $where = $this->buildWhereExpr($where);
        }
        return $where ? ' WHERE ' . (string)$where : '';
    }

    /**
     * buildGroupBy
     * 解析GROUP BY子句
     * @param mixed $groupBy
     *  examples:
     *      'sex, age'
     *      array('sex', 'age')
     * @access protected
     * @return void
     */
    protected function buildGroupBy($groupBy) {
        if (is_array($groupBy)) {
            $groupBy = implode(', ', $groupBy);
        }
        return $groupBy ? ' GROUP BY ' . (string)$groupBy : '';
    }

    /**
     * buildHaving
     * 解析HAVING子句
     * @param mixed $having 参见buildWhereExpr()
     * @access protected
     * @return void
     */
    protected function buildHaving($having) {
        if (is_array($having)) {
            $having = $this->buildWhereExpr($having);
        }
        return $having ? ' HAVING ' . (string)$having : '';
    }

    /**
     * buildOrderBy
     * 解析排序子句
     * @param mixed $orderBy
     *  examples:
     *      'uid asc, name desc'
     *      array('uid asc', 'name desc')
     * @access protected
     * @return void
     */
    protected function buildOrderBy($orderBy) {
        if (is_array($orderBy)) {
            $orderBy = implode(', ', $orderBy);
        }
        return $orderBy ? ' ORDER BY ' . (string)$orderBy : '';
    }

    /**
     * buildLimit
     * 解析limit子句
     * @param int $limit 条数
     * @param int $offset 偏移
     * @access protected
     * @return string
     */
    protected function buildLimit($limit, $offset = 0) {
        $limitClause = '';
        if ($limit > 0) {
            $limitClause .= ' LIMIT ' . intval($limit);
            if ($offset > 0) {
                $limitClause .= ' OFFSET ' . intval($offset);
            }
        } else {
            $limitClause = sprintf(' LIMIT %d', self::DEFAULT_QUERY_LIMIT);
        }
        return $limitClause;
    }

    /**
     * buildValues 
     * 构造单条插入的values子句
     * @param mixed $vals 参考buildValueExpr()
     * @access protected
     * @return void
     */
    protected function buildValues($vals) {
        $valExpr = $this->buildValueExpr($vals);
        if ($valExpr) {
            return ' VALUES (' . $valExpr . ')';
        } else {
            return '';
        }
    }

    /**
     * buildMultiValues 
     * 构造批量插入的values子句
     * @param mixed $multiVals 
        array(
            array(
                'name' => 'goosman-lei',
                'id'   => '5012470',
            ),
            array(
                'name' => 'goosman-lei',
                'id'   => '5012470',
            ),
        )
     * @access protected
     * @return void
     */
    protected function buildMultiValues($multiVals) {
        $multiValsArr = array();
        foreach ($multiVals as $vals) {
            $valExpr = $this->buildValueExpr($vals);
            if ($valExpr) {
                $multiValsArr[] = '(' . $valExpr . ')';
            }
        }
        if ($multiValsArr) {
            return ' VALUES' . implode(',', $multiValsArr);
        } else {
            return '';
        }
    }

    /**
     * buildSet 
     * 
     * @param mixed $sets 
        array(
            array('name', 'goosman-lei'),
            array('id', 5012470),
            array(':literal', 'age = age+1'),
        )
     * @access protected
     * @return void
     */
    protected function buildSet($sets) {
        $setExpr = $this->buildSetExpr($sets);
        if ($setExpr) {
            return ' SET ' . $setExpr;
        } else {
            return '';
        }
    }

    /**
     * buildOnDup 
     * 
     * @param mixed $vals 参考buildValueExpr()
     * @param mixed $onDup 
        样式1: array('field_name_1', 'field_name_2', ..., '')
        样式2: 'field_name_1 = xxx, field_name_2 = xxx'
     * @access protected
     * @return void
     */
    protected function buildOnDup($vals, $onDup = FALSE) {
        $onDupClause = '';
        if ($onDup) {
            if (is_array($onDup)) {
                $onDupFields = array();
                foreach ($onDup as $fieldName) {
                    if (isset($vals[$fieldName])) {
                        $onDupFields[] = array($fieldName, $vals[$fieldName]);
                    }
                }
                $onDupClause = ' ON DUPLICATE KEY UPDATE ' . $this->buildSetExpr($onDupFields);
            } else {
                $onDupClause = ' ON DUPLICATE KEY UPDATE ' . $onDup;
            }
        }
        return $onDupClause;
    }


    /**
     * buildSetExpr 
     * 
     * @param mixed $vals 
        array(
            array('name', 'goosman-lei'),
            array('id', 5012470),
            array(':literal', 'age = age+1'),
        )
     * @access protected
     * @return void
     */
    protected function buildSetExpr($vals) {
        $setExprArr = array();
        foreach ($vals as $val) {
            @list($fieldName, $fieldValue) = $val;
            if ($fieldName === ':literal') {
                $setExprArr[] = $fieldValue;
            } else {
                $setExprArr[] = $this->escapeFieldName($fieldName) . ' = ' . $this->escapeFieldValue($fieldName, $fieldValue);
            }
        }
        return implode(',', $setExprArr);
    }

    /**
     * buildValueNameExpr 
     * 
     * @param mixed $vals 
        array(
            'name' => 'goosman-lei',
            'id' => 5012470,
            'age' => 18,
        ),
     * @access protected
     * @return void
     */
    protected function buildValueNameExpr($vals) {
        $valExprArr = array();
        foreach ($vals as $fieldName => $fieldValue) {
            $valExprArr[] = $this->escapeFieldName($fieldName);
        }
        return implode(',', $valExprArr);
    }

    /**
     * buildValueExpr 
     * 
     * @param mixed $vals 
        array(
            'name' => 'goosman-lei',
            'id' => 5012470,
            'age' => 18,
        ),
     * @access protected
     * @return void
     */
    protected function buildValueExpr($vals) {
        $valExprArr = array();
        foreach ($vals as $fieldName => $fieldValue) {
            $valExprArr[] = $this->escapeFieldValue($fieldName, $fieldValue);
        }
        return implode(',', $valExprArr);
    }

    /**
     * buildWhereExpr
     * 解析表达式
     * @param mixed $conds 条件表达式
     Demo Input: array(
            array('score', 80),   // :eq 操作符可忽略. 第二个元素是非数组, 默认:eq
            array('pass', array(TRUE, FALSE)), // :in 操作符可忽略. 第二个元素是数组默认:in
            array(':ne', 'score', 80),
            array(':gt', 'score', 80),
            array(':ge', 'score', 80),
            array(':le', 'score', 80),
            array(':lt', 'score', 80),
            array(':between', 'score', 80, 100),
            array(':lt', 'score', 8.3),
            array(':eq', 'name', 'Jack'),
            array(':like_literal', 'name', 'Jack%'),
            array(':like_begin', 'name', 'Jack%'),
            array(':like_end', 'name', 'Jack%'),
            array(':like_entire', 'name', 'Jack%'),
            array(':or', array(
                array(':notlike_literal', 'name', 'Jack_'),
                array(':notlike_begin', 'name', 'Jack_'),
                array(':notlike_end', 'name', 'Jack_'),
                array(':notlike_entire', 'name', 'Jack_'),
                array(':in', 'sex', array('man', 'woman')),
                array(':notin', 'color', array('blue', 'black')),
            )),
            array(':not', array(
                array(':notnull', 'money'),
            )),
            array(':and', array(
                array(':isnull', 'color'),
            )),
            array(':literal', 'uid = 100'),
        )
    Demo Output: 
         (`score` = 80)
         AND (`pass` IN (1,0))
         AND (`score` != 80)
         AND (`score` > 80)
         AND (`score` >= 80)
         AND (`score` <= 80)
         AND (`score` < 80)
         AND (`score` BETWEEN 80 AND 100)
         AND (`score` < 8)
         AND (`name` = 'Jack')
         AND (`name` LIKE 'Jack%')
         AND (`name` LIKE 'Jack%'%)
         AND (`name` LIKE %'Jack%')
         AND (`name` LIKE %'Jack%'%)
         OR ((`name` NOT LIKE 'Jack_')
             AND (`name` NOT LIKE 'Jack_'%)
             AND (`name` NOT LIKE %'Jack_')
             AND (`name` NOT LIKE %'Jack_'%)
             AND (`sex` IN ('man','woman'))
             AND (`color` NOT IN ('blue','black'))
         )
         AND NOT ((`money` IS NOT NULL))
         AND ((`color` IS NULL))
         AND (uid = 100)
     * @access protected
     * @return void
     */
    public function buildWhereExpr($conds) {
        $exprArr = array();
        if (is_string($conds)) {
            return $conds;
        }
        if (!is_array($conds) || empty($conds)) {
            return '';
        }
        if(!is_array($conds[0])){
            $conds = array($conds);
        }
        foreach ($conds as $cond) {
            $prefix = 'AND';

            if (isset($cond[0][0]) && $cond[0][0] === ':') {
                $op = substr($cond[0], 1);
                array_shift($cond);
            } else {
                $op = is_array(@$cond[1]) ? 'in' : 'eq';
            }

            if (!$op) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'error' => 'have no op',
                    'cond'  => $cond,
                ), \F_ECode::QUERY_BUILD_EXPR_ERROR, 2);
                continue;
            }
            switch ($op) {
                /* 逻辑运算 */
                case 'or':
                    $prefix = 'OR';
                    $exprStr = $this->buildWhereExpr($cond[0]);
                    break;
                case 'and':
                    $prefix = 'AND';
                    $exprStr = $this->buildWhereExpr($cond[0]);
                    break;
                case 'not':
                    $prefix = 'AND NOT';
                    $exprStr = $this->buildWhereExpr($cond[0]);
                    break;
                /* 单目无类型运算符 */
                case 'isnull':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' IS NULL';
                    break;
                case 'notnull':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' IS NOT NULL';
                    break;
                case 'literal':
                    $exprStr = $cond[0];
                    break;
                /* 双目运算 */
                case 'ne':
                case '!=':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' != ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'gt':
                case '>':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' > ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'ge':
                case '>=':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' >= ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'eq':
                case '=':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' = ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'le':
                case '<=':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' <= ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'lt':
                case '<':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' < ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'like_literal':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' LIKE ' . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'like_begin':
                    $exprStr = $this->escapeFieldName($cond[0]) . " LIKE '" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "%'";
                    break;
                case 'like_end':
                    $exprStr = $this->escapeFieldName($cond[0]) . " LIKE '%" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "'";
                    break;
                case 'like_entire':
                    $exprStr = $this->escapeFieldName($cond[0]) . " LIKE '%" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "%'";
                    break;
                case 'notlike_literal':
                    $exprStr = $this->escapeFieldName($cond[0]) . " NOT LIKE " . $this->escapeFieldValue($cond[0], $cond[1]);
                    break;
                case 'notlike_begin':
                    $exprStr = $this->escapeFieldName($cond[0]) . " NOT LIKE '" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "%'";
                    break;
                case 'notlike_end':
                    $exprStr = $this->escapeFieldName($cond[0]) . " NOT LIKE '%" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "'";
                    break;
                case 'notlike_entire':
                    $exprStr = $this->escapeFieldName($cond[0]) . " NOT LIKE '%" . $this->escapeFieldValue($cond[0], $cond[1], TRUE, FALSE) . "%'";
                    break;
                /* 三目运算 */
                case 'between':
                    $exprStr = $this->escapeFieldName($cond[0]) . ' BETWEEN ' . $this->escapeFieldValue($cond[0], $cond[1]) . ' AND ' . $this->escapeFieldValue($cond[0], $cond[2]);
                    break;
                /* 集合运算 */
                case 'in':
                    if (!is_array($cond[1]) || empty($cond[1])) {
                        $exprStr = $this->escapeFieldName($cond[0]) . ' IN (null)';
                        break;
                    }
                    foreach ($cond[1] as $idx => $val) {
                        $cond[1][$idx] = $this->escapeFieldValue($cond[0], $val);
                    }
                    $exprStr = $this->escapeFieldName($cond[0]) . ' IN (' . implode(',', $cond[1]) . ')';
                    break;
                case 'notin':
                    if (!is_array($cond[1]) || empty($cond[1])) {
                        $exprStr = $this->escapeFieldName($cond[0]) . ' NOT IN (null)';
                        break;
                    }
                    foreach ($cond[1] as $idx => $val) {
                        $cond[1][$idx] = $this->escapeFieldValue($cond[0], $val);
                    }
                    $exprStr = $this->escapeFieldName($cond[0]) . ' NOT IN (' . implode(',', $cond[1]) . ')';
                    break;
                default:
                    \F_Ice::$ins->mainApp->logger_comm->warn(array(
                        'error' => 'unknown op',
                        'op'    => $op,
                        'cond'  => $cond,
                    ), \F_ECode::QUERY_BUILD_EXPR_ERROR);
                    break;
            }
            $exprArr[] = $prefix . ' (' . $exprStr . ')';
        }
        return preg_replace(';^\w+\s+;', '', implode(' ', $exprArr));
    }


    public function escapeFieldName($fieldName) {
        if (preg_match(';^\w+$;', $fieldName)) {
            return '`' . $fieldName . '`';
        } else {
            return $fieldName;
        }
    }

    public function escapeFieldValue($fieldName, $fieldValue, $usedForLike = FALSE, $addBoundary = TRUE) {
        if (!isset($this->mapping[$fieldName])) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'error'         => 'no mapping field',
                'field_name'    => $fieldName,
                'field_value'   => $fieldValue,
                'used_for_like' => $usedForLike,
                'add_boundary'  => $addBoundary,
            ), \F_ECode::QUERY_ESCAPE_FIELD_VALUE_FAILED);
            return $this->escapeString($fieldValue, $usedForLike, $addBoundary);
        }
        switch ($this->mapping[$fieldName]) {
            case 'i': // 整型
                return $this->escapeInt($fieldValue);
            case 'f': // 浮点
                return $this->escapeFloat($fieldValue);
            case 's': // 普通字符串
                return $this->escapeString($fieldValue, $usedForLike, $addBoundary);
            case 'I': // 标识符字符串
                return $this->escapeId($fieldValue, $addBoundary);
            default:
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'error'         => 'unknown field type',
                    'field_name'    => $fieldName,
                    'field_value'   => $fieldValue,
                    'used_for_like' => $usedForLike,
                    'add_boundary'  => $addBoundary,
                ), \F_ECode::QUERY_ESCAPE_FIELD_VALUE_FAILED);
                return $this->escapeString($fieldValue, $usedForLike, $addBoundary);
        }
    }

    public function escapeString($value, $usedForLike = FALSE, $addBoundary = TRUE) {
        $boundary = $addBoundary ? "'" : '';
        if ($usedForLike) {
            return $boundary . addcslashes((string)$value, "\\\"'\0%_") . $boundary; # add slashes for ('), ("), (\), (NULL byte), (%), (_)
        } else {
            return $boundary . addslashes((string)$value) . $boundary; # add slashes for ('), ("), (\), (NULL byte)
        }
    }

    public function escapeId($value, $addBoundary = TRUE) {
        $boundary = $addBoundary ? "'" : '';
        return $boundary . preg_replace(';\W++;', '', strval($value)) . $boundary;
    }

    public function escapeInt($value) {
        return (int)$value;
    }

    public function escapeFloat($value) {
        return (float)$value;
    }

    /**
     * getTableName 
     * 
     * @param mixed $where 增加分表策略时使用. 目前暂时不使用
     * @access public
     * @return void
     */
    public function getTableName($where = array()) {
        return $this->tableName;
    }
}
