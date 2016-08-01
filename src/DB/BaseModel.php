<?php
namespace Ice\DB;
class BaseModel extends \Ice_DB_Query {
    
    protected $pk = 'id'; //主键
    protected $autoAddTimeField = 'add_time';//需要在添加记录时自动处理为当前时间的字段，没有或不需要可定义为空
    protected $autoUpdteTimeField = 'update_time';//需要在更新记录时自动处理为当前时间的字段，没有或不需要可定义为空
    
    protected $tableName = '';
    protected $dbResource = '';

    protected $mapping = [];

    /**
     * 添加数据
     * @param array $data
     * @return 失败 FALSE
     */
    public function add($data){
        $insertData = [];
        $returnValue = 0;
        foreach ($data as $k => $v) {
            if (isset($this->mapping[$k])) {
                $insertData[$k] = $v;
            }
        }
        if ($insertData) {
            if($this->autoAddTimeField && isset($this->mapping[$this->autoAddTimeField])){
                $insertData[$this->autoAddTimeField] = time();
            }
            $returnValue = $this->insert($insertData);
        }
        return $returnValue;
    }
    /**
     * 根据一个关联数组条件获取单条数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     */
    public function getInfoByAssoc($data) {
        $returnData = false;

        if($data) {
            $where = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $where[] = [$k, $v];
                }
            }
            if($where){
                $returnData = $this->getRow($where);
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

        if($pks) {
            $where = [
                [':in', $this->pk, $pks],
            ];
            if($where){
                $returnData = $this->getRows($where);
            }
        }

        return $returnData;
    }
    
    /**
     * 根据一个关联数组条件获取列表数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     */
    public function getListByAssoc($data, $limit = FALSE, $offset = 0, $orderBy = FALSE) {
        $returnData = [];

        if($data) {
            $where = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    //数组自动处理为in
                    if(is_array($v)){
                        [':in', $k, $v];
                    }else{
                        $where[] = [$k, $v];
                    }
                }
            }
            if($where){
                $returnData = $this->getRows($where, '*', $limit, $offset, $orderBy);
            }
        }

        return $returnData;
    }
    
    /**
     * 根据一个关联数组条件更新数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 新数据
     * @param array $assoc 条件
     * @return 影响的行数，失败false
     */
    public function updateByAssoc($data, $assoc){
        $updateData = [];
        $returnValue = 0;
        foreach ($data as $k => $v) {
            if (isset($this->mapping[$k])) {
                $updateData[] = [$k, $v];
            }
        }
        if ($updateData && $assoc) {

            $where = [];
            foreach ($assoc as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $where[] = [$k, $v];
                }
            }
            
            //自动处理更新时间字段
            if($this->autoUpdteTimeField && isset($this->mapping[$this->autoUpdteTimeField])){
                $updateData[] = [$this->autoUpdteTimeField, time()];
            }
            $returnValue = $this->update($updateData, $where);
        }
        return $returnValue;
    }
    
    /**
     * 根据主键更新数据
     * @param array $data
     * @param str $pk 主键值
     * @return 影响的行数，失败false
     */
    public function updateByPk($data, $pk){
        return $this->updateByAssoc($data, [$this->pk => $pk]);
    }
    
    /**
     * 根据一个关联数组条件删除数据（将关联数组拼为 k1=v1 AND k2=v2 格式条件）
     * @param array $data 条件
     * @return 影响的行数
     */
    public function deleteByAssoc($data){
        $returnValue = 0;
        if ($data) {
            $where = [];
            foreach ($data as $k => $v) {
                if (isset($this->mapping[$k])) {
                    $where[] = [$k, $v];
                }
            }
            if($where){
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
    public function deleteByPk($pk){
        return $this->deleteByAssoc([$this->pk => $pk]);
    }
    
    /**
     * 根据主键设置某列数据
     * @param array $id
     * @return 影响的行数
     */
    public function setFieldByPk($field, $value, $pk){
        return $this->updateByPk([$field => $value], $pk);
    }
    
    /**
     * 根据主键给某字段+1
     * @param array $id
     * @return 影响的行数
     */
    public function incrFieldByPk($field, $pk, $step = 1){
        $updateData = [];
        $returnValue = 0;
        $step = intval($step);

        if (isset($this->mapping[$field]) && $step) {
            $updateData[] = [':literal', "{$field} = {$field}+{$step}"];
        }
        
        if ($updateData && $pk) {
            $where = [
                [$this->pk, $pk],
            ];
            
            if($this->autoUpdteTimeField && isset($this->mapping[$this->autoUpdteTimeField])){
                $updateData[] = [$this->autoUpdteTimeField, time()];
            }
            $returnValue = $this->update($updateData, $where);
        }
        return $returnValue;
    }
    
    /**
     * 根据主键给某字段-1
     * @param array $id
     * @return 影响的行数
     */
    public function decrFieldByPk($field, $pk, $step = 1){
        return $this->incrFieldByPk($field, $pk, -$step);
    }
}
