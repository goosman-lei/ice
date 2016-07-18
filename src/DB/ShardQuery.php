<?php
namespace Ice\DB;
class ShardQuery extends \Ice_DB_Query {

    protected $shardColumn;
    protected $shardKey;

    public function getRows($where = array(), $cols = '*', $limit = FALSE, $offset = 0, $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE) {
        foreach ($where as $idx => $cond) {
            if (isset($cond[0]) && $cond[0] != ':shard') {
                continue;
            }

            $shardValue = (array)$cond[1];
            break;
        }

        $resultArr    = array();
        $shardMapping = $this->getShardMapping($shardValue);
        foreach ($shardMapping as $shardKey => $inShardValues) {
            $where[$idx] = array($this->shardColumn, $inShardValues);
            $this->setShardKey($shardKey);
            $datas = parent::getRows($where, $cols, $limit, $offset, $orderBy, $join, $groupBy, $having, $tableOptions, $selectOptions);
            if (empty($datas)) {
                continue;
            }
            $resultArr = array_merge($resultArr, $datas);
        }
        return $resultArr;
    }

    public function update($setValues, $where = array(), $orderBy = FALSE, $limit = FALSE) {
         foreach ($where as $idx => $cond) {
            if (isset($cond[0]) && $cond[0] != ':shard') {
                continue;
            }

            $shardValue = (array)$cond[1];
            break;
        }

        $allAffect    = 0;
        $shardMapping = $this->getShardMapping($shardValue);
        foreach ($shardMapping as $shardKey => $inShardValues) {
            $where[$idx] = array($this->shardColumn, $inShardValues);
            $this->setShardKey($shardKey);
            $allAffect += parent::update($setValues, $where, $orderBy, $limit);
        }
        return $allAffect;
    }

    public function insert($setValues, $onDup = FALSE) {
         foreach ($setValues as $idx => $val) {
            if (isset($val[0]) && $val[0] != ':shard') {
                continue;
            }

            $shardValue = $val[1];
            break;
        }

        $setValues[$idx] = array($this->shardColumn, $shardValue);
        $shardKey = $this->getShardKey($shardValue);
        $this->setShardKey($shardKey);

        return parent::insert($setValues, $onDup);
    }

    public function multiInsert($setValues) {
        $shardMapping = array();
        foreach ($setValues as $idx => $val) {
            foreach ($val as $k => $v) {
                if (isset($val[0]) && $val[0] != ':shard') {
                    continue;
                }

                $shardValue = $val[1];
                break;
            }

            $val[$k] = array($this->shardColumn, $shardValue);
            $shardKey = $this->getShardKey($shardValue);
            $shardMapping[$shardKey][] = $val;
        }

        $allAffect    = 0;
        foreach ($shardMapping as $shardKey => $inShardSetValues) {
            $this->setShardKey($shardKey);
            $allAffect += parent::multiInsert($inShardSetValues);
        }
        return $allAffect;
    }

    public function replace($setValues) {
         foreach ($setValues as $idx => $val) {
            if (isset($val[0]) && $val[0] != ':shard') {
                continue;
            }

            $shardValue = $val[1];
            break;
        }

        $setValues[$idx] = array($this->shardColumn, $shardValue);
        $shardKey = $this->getShardKey($shardValue);
        $this->setShardKey($shardKey);

        return parent::replace($setValues);
    }

    public function delete($where = array(), $orderBy = FALSE, $limit = FALSE) {
         foreach ($where as $idx => $cond) {
            if (isset($cond[0]) && $cond[0] != ':shard') {
                continue;
            }

            $shardValue = (array)$cond[1];
            break;
        }

        $allAffect    = 0;
        $shardMapping = $this->getShardMapping($shardValue);
        foreach ($shardMapping as $shardKey => $inShardValues) {
            $where[$idx] = array($this->shardColumn, $inShardValues);
            $this->setShardKey($shardKey);
            $allAffect += parent::delete($where, $orderBy, $limit);
        }
        return $allAffect;

    }

    public function getShardMapping($shardValue) {
        $shardMapping = array();
        foreach ($shardValue as $value) {
            $shardKey = $this->getShardKey($value);
            $shardMapping[$shardKey][] = $value;
        }
        return $shardMapping;
    }

    public function getShardKey($value) {
        return '';
    }

    public function getTableName() {
        return $this->tableName . '_' . $this->shardKey;
    }
}
