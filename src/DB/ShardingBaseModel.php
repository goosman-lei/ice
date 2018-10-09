<?php

namespace Ice\DB;

class ShardingBaseModel extends ShardQueryV2 {

    protected $pk = 'id'; //主键
    protected $autoAddTimeField = 'add_time'; //需要在添加记录时自动处理为当前时间的字段，没有或不需要可定义为空
    protected $autoUpdteTimeField = 'update_time'; //需要在更新记录时自动处理为当前时间的字段，没有或不需要可定义为空
    protected $tableName = '';
    protected $dbResource = '';
    protected $mapping = [];
    protected $convert = [];

    /**
     * 根据运行时赋值字段处理字段
     * @param array $data 要处理的数据
     * @param enum $runtime add|update
     */
    protected function assignAutoField(&$data, $runtime) {
        $autoAssignMap = [
            'add'    => [$this->autoAddTimeField, $this->autoUpdteTimeField],
            'update' => [$this->autoUpdteTimeField],
        ];

        if ($data && is_array($data) && isset($autoAssignMap[$runtime])) {
            if (isset($data[0]) && count($data[0]) >= 2) {
                //已拼为setValues的data
                $keys = array_column($data, 0);
                foreach ($autoAssignMap[$runtime] as $field) {
                    if ($field && isset($this->mapping[$field]) && !in_array($field, $keys)) {
                        $data[] = [$field, time()];
                    }
                }
            } else {
                //键值对data
                foreach ($autoAssignMap[$runtime] as $field) {
                    if ($field && isset($this->mapping[$field]) && !isset($data[$field])) {
                        $data[$field] = time();
                    }
                }
            }
        }
    }

    /**
     * 添加数据
     * @param array $data
     * @return 失败 FALSE
     */
    public function add($data) {
        $returnValue = false;

        if ($data && is_array($data)) {
            $this->assignAutoField($data, 'add');
            $insertData = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $insertData[$k] = $v;
                }
            }
            if ($insertData) {
                $returnValue = $this->insert($insertData);
            }
        }
        return $returnValue;
    }

    /**
     * 根据一个关联数组条件获取单条数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     */
    public function getInfoByAssoc($data, $cols = '*', $orderBy = false) {
        $returnData = false;

        if ($data && is_array($data)) {
            $where = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $where[] = [$k, $v];
                }
            }
            if ($where) {
                $returnData = $this->getRow($where, $cols, $orderBy);
            }
        }

        return $returnData;
    }

    /**
     * 根据主键获取单条信息
     * @param str $pk 主键值
     */
    public function getInfoByPk($pk) {
        return $this->getInfoByAssoc([$this->pk => $pk]);
    }

    /**
     * 根据多个主键批量获取信息列表
     * @param array $pks 主键列表
     */
    public function getListByPks($pks) {
        $returnData = [];

        if ($pks && is_array($pks)) {
            $where = [
                [':in', $this->pk, $pks],
            ];
            if ($where) {
                $returnData = $this->getRows($where);
            }
        }

        return $returnData;
    }

    /**
     * 根据一个关联数组条件获取列表数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     */
    public function getListByAssoc($data, $limit = FALSE, $offset = 0, $orderBy = FALSE, $cols = '*') {
        $returnData = [];

        $where = [];
        if ($data && is_array($data)) {
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    //数组自动处理为in
                    if (is_array($v)) {
                        $where[] = [':in', $k, $v];
                    } else {
                        $where[] = [$k, $v];
                    }
                }
            }
        }
        $returnData = $this->getRows($where, $cols, $limit, $offset, $orderBy);

        return $returnData;
    }

    /**
     * 根据一个关联数组条件更新数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 新数据
     * @param array $assoc 条件
     * @return 影响的行数，失败false
     */
    public function updateByAssoc($data, $assoc) {
        $returnValue = false;

        if ($data && $assoc && is_array($data) && is_array($assoc)) {
            $this->assignAutoField($data, 'update');
            $updateData = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $updateData[] = [$k, $v];
                }
            }
            if ($updateData) {

                $where = [];
                foreach ($assoc as $k => $v) {
                    if (isset($this->mapping[$k])) {
                        if(is_array($v)){
                            $where[] = [':in', $k, $v];
                        }else{
                            $where[] = [$k, $v];
                        }
                    }
                }

                $returnValue = $this->update($updateData, $where);
            }
        }
        return $returnValue;
    }

    /**
     * 根据主键更新数据
     * @param array $data
     * @param str $pk 主键值
     * @return 影响的行数，失败false
     */
    public function updateByPk($data, $pk) {
        return $this->updateByAssoc($data, [$this->pk => $pk]);
    }

    /**
     * 根据一个关联数组条件删除数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     * @return 影响的行数
     */
    public function deleteByAssoc($data) {
        $returnValue = false;
        if ($data && is_array($data)) {
            $where = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $where[] = [$k, $v];
                }
            }
            if ($where) {
                $returnValue = $this->delete($where);
            }
        }
        return $returnValue;
    }

    /**
     * 根据主键删除数据
     * @param str $pk 主键值
     * @return 影响的行数
     */
    public function deleteByPk($pk) {
        return $this->deleteByAssoc([$this->pk => $pk]);
    }

    /**
     * 根据主键设置某列数据
     * @param array $id
     * @return 影响的行数
     */
    public function setFieldByPk($field, $value, $pk) {
        return $this->updateByPk([$field => $value], $pk);
    }

    /**
     * 根据主键给某字段+1
     * @param array $id
     * @return 影响的行数
     */
    public function incrFieldByPk($field, $pk, $step = 1) {
        $updateData = [];
        $returnValue = false;
        $step = intval($step);

        if (isset($this->mapping[$field]) && $step) {
            $updateData[] = [':literal', "{$field} = {$field}+{$step}"];
        }

        if ($updateData && $pk) {
            $where = [
                [$this->pk, $pk],
            ];
            $this->assignAutoField($updateData, 'update');
            $returnValue = $this->update($updateData, $where);
        }
        return $returnValue;
    }

    /**
     * 根据主键给某字段-1
     * @param array $id
     * @return 影响的行数
     */
    public function decrFieldByPk($field, $pk, $step = 1) {
        return $this->incrFieldByPk($field, $pk, -$step);
    }

    /**
     * convertDataOut 
     * 根据convert转化输出数据
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function convertDataOut($data) {
        foreach ($data as $key => $val) {
            if (isset($this->convert[$key])) {
                if ('json' == $this->convert[$key]) {
                    $data[$key] = json_decode($data[$key], true);
                }
            }
        }
        return $data;
    }

    /**
     * convertDataIn 
     * 根据convert转化输入数据
     * @param mixed $data 
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function convertDataIn($data) {
        foreach ($data as $key => $val) {
            if (isset($this->convert[$key])) {
                if ('json' == $this->convert[$key]) {
                    $data[$key] = json_encode($data[$key]);
                }
            }
        }
        return $data;
    }

}
