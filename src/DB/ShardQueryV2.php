<?php
namespace Ice\DB;
/**
 * ShardQueryV2 
 * 数据库表级分片第二版. 核心设计思想:
 *   * 将分片逻辑与底层数据查询接口完全分离
 *   * 独立的Sharding抽象, 描述分片过程
 *       * Sharding.args: 此分片对应的输入参数列表. 一个分片实例上可绑定多组参数列表
 *       * Sharding.name: 分片名. (建议每个表的分片, 使用独立的类描述, 因此, 将分片名放入到Sharding中.
 *       * Sharding.index: 分片的逻辑结果
 *   * 各接口含义:
 *      * getTableName():
 *          与上层类Ice_DB_Query衔接, 供查询时产生类名.
 *      * applySharding():
 *          分片逻辑与查询接扣的衔接, 应用一个分片实例
 *      * sharding():
 *          单条记录分片计算. 返回一个\Ice\DB\Sharding实例
 *      * shardings():
 *          多条记录分片计算. 返回多个\Ice\DB\Sharding实例
 *          {
 *              "Sharding-Name-1": new \Ice\DB\Sharding(),
 *              "Sharding-Name-2": new \Ice\DB\Sharding(),
 *              ...
 *          }
 *          同时, 每个Sharding实例中, args成员变量绑定了多组分片参数
 *  * 实例演示:
 *      * 场景: 表User使用group_id和uid进行分组
 *      * 分片逻辑: sprintf('table_%d', (ord(group_id) + uid) % 2) (组名ASCII码 + UID 取模2)
 *      * 期望查询: Query($gid = [A, B, C, D, E], $uid = [1, 2, 4, 7, 5]);
 *      * 分片: Shardings($gid = [A, B, C, D, E], $uid = [1, 2, 4, 7, 5]);
 *          返回: [
 *              "table_0" => new \Ice\DB\Sharding{
 *                  args: [
 *                      [gid = A, uid = 1],
 *                      [gid = B, uid = 2],
 *                      [gid = E, uid = 5],
 *                  ]
 *              },
 *              "table_1" => new \Ice\DB\Sharding{
 *                  args: [
 *                      [gid = C, uid = 4],
 *                      [gid = D, uid = 7],
 *                  ]
 *              },
 *          ]
 *      * 应用: 应用每个分片, 及其参数, 构造查询
 *       
 * 优点: sharding仅关注数据分片过程, 数据表的调度由Model层自由控制, 灵活性更高.
 * 缺点: 涉及多分片的逻辑, 都需要自己手动编写
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class ShardQueryV2 extends \Ice_DB_Query {

    protected $shardingClass;
    protected $shardingObj;

    public function getTableName($where = array()) {
        return $this->shardingObj->getName();
    }

    protected function applySharding(\Ice\DB\Sharding $shardingObj) {
        $this->shardingObj = $shardingObj;
    }

    protected function sharding() {
        $shardingClass  = $this->shardingClass;
        return new $shardingClass(func_get_args());
    }

    /**
     * shardings 
     * [], [], []
     * @access protected
     * @return void
     */
    protected function shardings() {
        $all_args = func_get_args();
        // 取最大参数组长度
        $group_n  = 1;
        $arg_n    = count($all_args);
        foreach ($all_args as $idx => $onearg) {
            if (is_array($onearg)) {
                $group_n = max($group_n, count($onearg));
            }
        }

        $shardings = [];
        for ($i = 0; $i < $group_n; $i ++) {
            $args = [];
            for ($j = 0; $j < $arg_n; $j ++) {
                if (is_array($all_args[$j])) {
                    // 要么就是对应下标. 要么就是最后一个元素
                    $args[] = $all_args[$j][min(count($all_args[$j]) - 1, $i)];
                } else {
                    $args[] = $all_args[$j];
                }
            }
            $sharding_name = call_user_func_array([$this->shardingClass, 'shardingName'], $args);
            if (isset($shardings[$sharding_name])) {
                $shardings[$sharding_name]->pushArgs($args);
            } else {
                $shardingClass  = $this->shardingClass;
                $shardings[$sharding_name] = new $shardingClass($args);
            }
        }
        return $shardings;
    }
}
