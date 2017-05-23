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


    /**
     * 该方法的使用场景为数据表的分表方式具有严格的时间序,
     * 某些使用场景下要求在某些特定时间范围内的进行查找,
     * 可以通过时间先确定分表范围在进行查询。
     * 通过起止时间来确定分表范围。
     * rang_start_index 与 rang_end_index 没有严格的大小关系
     * 当返回的数据需要按照时间正序排列时, range_start_index 小于等于 range_end_index
     * 反之,range_start_index 大于等于 range_end_index
     * max_table_index为当前最大分表index,必需,防止index递增时越界
     *  $range = array(
     *      'range_start_index' => $start_index,    // 开始查询的index
     *      'range_end_index'   => $end_index,      // 结束查询的index
     *      'max_table_index'   => $max_index,      // 当前分表的最大index,防止越界
     *      'next_direction'    => $direction,      // 迭代方向,为True时index递增,为False时index值递减
     *      'limit'             => $limit,          // 数量约束,暂时未使用
     *  );
     */
    public function getRowsRange($range, $where = array(), $cols = '*', $limit = FALSE, $offset = 0, $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE){
        $reverse = isset($range['next_direction']) ? $range['next_direction'] : false;
        $this->setShardKey($range['range_start_index']);
        $tmpLimit = $limit;
        $tmpOffset = $offset;
        $resultArr = array();
        do{
            $datas = parent::getRows($where, $cols, $tmpLimit, $tmpOffset, $orderBy, $join, $groupBy, $having, $tableOptions, $selectOptions);
            if(!empty($datas)){
                $resultArr = array_merge($resultArr, $datas);
            }
            if($this->shardKey == $range['range_end_index']){
                break;
            }
            if($limit){
                $tmpLimit = $limit - count($resultArr);
                if($tmpLimit <= 0){
                    break;
                }
            }
            if($tmpOffset){
                $curOffsetCount = parent::getRows($where, 'count(1) AS count', 1);
                $curOffsetCount = $curOffsetCount[0]['count'];
                $tmpOffset = ($tmpOffset - intval($curOffsetCount) <= 0) ? 0 : ($tmpOffset - intval($curOffsetCount));
            }
            $hasNextTable = $this->getNextTable($reverse, $range['max_table_index']);
            if(!$hasNextTable){
                break;
            }
        }while(true);
        return $resultArr;
    }

    /**
     * 指定一定的分表索引进行查询,为了满足,表之间没有严格顺序的情况。
     */
    public function getRowsTableIndexs($indexArray, $where = array(), $cols = '*', $limit = FALSE, $offset = 0, $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE){
        $tmpLimit = $limit;
        $tmpOffset = $offset;
        $resultArr = array();
        foreach($indexArray as $index) {
            $this->setShardKey($index);
            $datas = parent::getRows($where, $cols, $tmpLimit, $tmpOffset, $orderBy, $join, $groupBy, $having, $tableOptions, $selectOptions);
            if(!empty($datas)){
                $resultArr = array_merge($resultArr, $datas);
            }
            if($limit){
                $tmpLimit = $limit - count($resultArr);
                if($tmpLimit <= 0){
                    break;
                }
            }
            if($tmpOffset){
                $curOffsetCount = parent::getRows($where, 'count(1) AS count', 1);
                $curOffsetCount = $curOffsetCount[0]['count'];
                $tmpOffset = ($tmpOffset - intval($curOffsetCount) <= 0) ? 0 : ($tmpOffset - intval($curOffsetCount));
            }
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
         foreach ($setValues as $key => $val) {
            if ($this->shardColumn != $key) {
                continue;
            }

            $shardValue = $val;
            break;
        }

        $shardKey = $this->getShardKey($shardValue);
        $this->setShardKey($shardKey);

        return parent::insert($setValues, $onDup);
    }

    public function multiInsert($setValues) {
        $shardMapping = array();
        foreach ($setValues as $idx => $val) {
            foreach ($val as $k => $v) {
                if ($this->shardColumn != $k) {
                    continue;
                }

                $shardValue = $v;
                break;
            }

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
        foreach ($setValues as $key => $val) {
            if ($this->shardColumn != $key) {
                continue;
            }

            $shardValue = $val;
            break;
        }

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

    public function getNextTable($direction, $maxTableIndex){
        if($direction){
            $this->shardKey++;
            if($this->shardKey > $maxTableIndex){
                return false;
            }
        }else{
            $this->shardKey--;
            if($this->shardKey < 0){
                return false;
            }
        }
        return true;
    }

    public function setShardKey($shardKey) {
        $this->shardKey = $shardKey;
        return true;
    }

    public function getTableName($where = array()) {
        return $this->tableName . '_' . $this->shardKey;
    }
}
