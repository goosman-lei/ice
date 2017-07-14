<?php
namespace Ice\Resource\Helper;


/**
 * 区分主从的redis连接类
 */
class Redis {
    
    protected $resource = null;
    
    const CLUSTER_MASTER  = 'master';
    const CLUSTER_SLAVE   = 'slave';
    
    protected $handlers = [];


    public function __construct($resource) {
        $this->resource = $resource;
    }

    public function __call($method, $parameters) {
        // 命令合法性检查
        $method = strtolower($method);
        //判断命令读写区分cluster
        $cluster = isset($this->clusterMapping[$method]) ? $this->clusterMapping[$method] : self::CLUSTER_MASTER;

        try {
            $this->initHandler($cluster);
            $mockMethod = 'mock' . ucfirst($method);
            if (method_exists($this, $mockMethod)) {
                $resp = call_user_func_array(array($this, $mockMethod), $parameters);
            }else {
                $resp = call_user_func_array(array($this->handlers[$cluster], $method), $parameters);
            }
        } catch (\RedisException $e) {
            $resp = FALSE;
        }
        return $resp;
    }
    
    protected function initHandler($cluster){
        if(!isset($this->handlers[$cluster])){
            $dsn = 'redis://' . $this->resource . '/' . $cluster;
            $this->handlers[$cluster] = \F_Ice::$ins->workApp->proxy_resource->get($dsn);
        }
    }

    /**
     * 针对量特别大的mget操作的优化,暂定分为100个一组
     * @param $keys
     * @return array
     */
    public function mockMget($keys) {
        $keys = array_values($keys);
        if (empty($keys)) {
            return array();
        }
        $orginMethod = 'mget';
        //判断命令读写区分cluster
        $cluster = isset($this->clusterMapping[$orginMethod]) ? $this->clusterMapping[$orginMethod] : self::CLUSTER_MASTER;
        $keysGroup = array_chunk($keys, 100);
        $retsGroup = array();
        foreach($keysGroup as $keyGroup) {
            $paras = array($keyGroup);
            $ret = call_user_func_array(array($this->handlers[$cluster], $orginMethod), $paras);
            if (count($ret) != count($keyGroup)) {
                $ret = array_fill(0, count($keyGroup), FALSE);
            }
            $retsGroup = array_merge($retsGroup, $ret);
        }
        return $retsGroup;
    }

    /**
     * $clusterMapping
     * redis命令主从映射
     * @var array
     * @access private
     */
    private $clusterMapping = array(
        /* keys command */
        'del'                   => self::CLUSTER_MASTER, 	//DEL key [key …]
        'dump'                  => self::CLUSTER_MASTER, 	//DUMP key
        'exists'                => self::CLUSTER_SLAVE, 	//EXISTS key
        'expire'                => self::CLUSTER_MASTER, 	//EXPIRE key seconds
        'expireat'              => self::CLUSTER_MASTER, 	//EXPIREAT key timestamp
        'persist'               => self::CLUSTER_MASTER, 	//PERSIST key
        'pexpire'               => self::CLUSTER_MASTER, 	//PEXPIRE key milliseconds
        'pexpireat'             => self::CLUSTER_MASTER, 	//PEXPIREAT key milliseconds-timestamp
        'pttl'                  => self::CLUSTER_SLAVE, 	//PTTL key
        'restore'               => self::CLUSTER_MASTER, 	//RESTORE key ttl serialized-value
        'sort'                  => self::CLUSTER_MASTER, 	//SORT key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC DESC] [ALPHA] [STORE destination]
        'ttl'                   => self::CLUSTER_SLAVE, 	//TTL key
        'type'                  => self::CLUSTER_SLAVE, 	//TYPE key

        /* strings command */
        'append'                => self::CLUSTER_MASTER, 	//APPEND key value
        'bitcount'              => self::CLUSTER_MASTER, 	//BITCOUNT key [start] [end]
        'decr'                  => self::CLUSTER_MASTER, 	//DECR key
        'decrby'                => self::CLUSTER_MASTER, 	//DECRBY key decrement
        'get'                   => self::CLUSTER_SLAVE, 	//GET key
        'getbit'                => self::CLUSTER_MASTER, 	//GETBIT key offset
        'getrange'              => self::CLUSTER_MASTER, 	//GETRANGE key start end
        'getset'                => self::CLUSTER_MASTER, 	//GETSET key value
        'incr'                  => self::CLUSTER_MASTER, 	//INCR key
        'incrby'                => self::CLUSTER_MASTER, 	//INCRBY key increment
        'incrbyfloat'           => self::CLUSTER_MASTER, 	//INCRBYFLOAT key increment
        'mget'                  => self::CLUSTER_SLAVE, 	//MGET key [key ...]
        'mset'                  => self::CLUSTER_MASTER, 	//MSET key value [key value ...]
        'psetex'                => self::CLUSTER_MASTER, 	//PSETEX key milliseconds value
        'set'                   => self::CLUSTER_MASTER, 	//SET key value [EX seconds] [PX milliseconds] [NX XX]
        'setbit'                => self::CLUSTER_MASTER, 	//SETBIT key offset value
        'setex'                 => self::CLUSTER_MASTER, 	//SETEX key seconds value
        'setnx'                 => self::CLUSTER_MASTER, 	//SETNX key value
        'setrange'              => self::CLUSTER_MASTER, 	//SETRANGE key offset value
        'strlen'                => self::CLUSTER_MASTER, 	//STRLEN key

        /* hashes command */
        'hdel'                  => self::CLUSTER_MASTER, 	//HDEL key field [field ...]
        'hexists'               => self::CLUSTER_SLAVE, 	//HEXISTS key field
        'hget'                  => self::CLUSTER_SLAVE, 	//HGET key field
        'hgetall'               => self::CLUSTER_SLAVE, 	//HGETALL key
        'hincrby'               => self::CLUSTER_MASTER, 	//HINCRBY key field increment
        'hincrbyfloat'          => self::CLUSTER_MASTER, 	//HINCRBYFLOAT key field increment
        'hkeys'                 => self::CLUSTER_MASTER, 	//HKEYS key
        'hlen'                  => self::CLUSTER_MASTER, 	//HLEN key
        'hmget'                 => self::CLUSTER_SLAVE, 	//HMGET key field [field ...]
        'hmset'                 => self::CLUSTER_MASTER, 	//HMSET key field value [field value ...]
        'hset'                  => self::CLUSTER_MASTER, 	//HSET key field value
        'hsetnx'                => self::CLUSTER_MASTER, 	//HSETNX key field value
        'hvals'                 => self::CLUSTER_MASTER, 	//HVALS key
        'hscan'                 => self::CLUSTER_MASTER, 	//HSCAN key cursor [MATCH pattern] [COUNT count]

        /* lists command */
        'lindex'                => self::CLUSTER_SLAVE, 	//LINDEX key index
        'linsert'               => self::CLUSTER_MASTER, 	//LINSERT key BEFORE AFTER pivot value
        'llen'                  => self::CLUSTER_MASTER, 	//LLEN key
        'lpop'                  => self::CLUSTER_MASTER, 	//LPOP key
        'lpush'                 => self::CLUSTER_MASTER, 	//LPUSH key value [value ...]
        'lpushx'                => self::CLUSTER_MASTER, 	//LPUSHX key value
        'lrange'                => self::CLUSTER_SLAVE, 	//LRANGE key start stop
        'lrem'                  => self::CLUSTER_MASTER, 	//LREM key count value
        'lset'                  => self::CLUSTER_MASTER, 	//LSET key index value
        'ltrim'                 => self::CLUSTER_MASTER, 	//LTRIM key start stop
        'rpop'                  => self::CLUSTER_MASTER, 	//RPOP key
        'rpoplpush'             => self::CLUSTER_MASTER, 	//RPOPLPUSH source destination
        'rpush'                 => self::CLUSTER_MASTER, 	//RPUSH key value [value ...]
        'rpushx'                => self::CLUSTER_MASTER, 	//RPUSHX key value

        /* sets command */
        'sadd'                  => self::CLUSTER_MASTER, 	//SADD key member [member ...]
        'scard'                 => self::CLUSTER_MASTER, 	//SCARD key
        'sdiff'                 => self::CLUSTER_MASTER, 	//SDIFF key [key ...]
        'sdiffstore'            => self::CLUSTER_MASTER, 	//SDIFFSTORE destination key [key ...]
        'sinter'                => self::CLUSTER_MASTER, 	//SINTER key [key ...]
        'sinterstore'           => self::CLUSTER_MASTER, 	//SINTERSTORE destination key [key ...]
        'sismember'             => self::CLUSTER_MASTER, 	//SISMEMBER key member
        'smembers'              => self::CLUSTER_MASTER, 	//SMEMBERS key
        'smove'                 => self::CLUSTER_MASTER, 	//SMOVE source destination member
        'spop'                  => self::CLUSTER_MASTER, 	//SPOP key
        'srandmember'           => self::CLUSTER_MASTER, 	//SRANDMEMBER key [count]
        'srem'                  => self::CLUSTER_MASTER, 	//SREM key member [member ...]
        'sunion'                => self::CLUSTER_MASTER, 	//SUNION key [key ...]
        'sunionstore'           => self::CLUSTER_MASTER, 	//SUNIONSTORE destination key [key ...]
        'sscan'                 => self::CLUSTER_MASTER, 	//SSCAN key cursor [MATCH pattern] [COUNT count]

        /* sorted sets command */
        'zadd'                  => self::CLUSTER_MASTER, 	//ZADD key score member [score] [member]
        'zcard'                 => self::CLUSTER_SLAVE, 	//ZCARD key
        'zcount'                => self::CLUSTER_MASTER, 	//ZCOUNT key min max
        'zincrby'               => self::CLUSTER_MASTER, 	//ZINCRBY key increment member
        'zinterstore'           => self::CLUSTER_MASTER, 	//ZINTERSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM MIN MAX]
        'zlexcount'             => self::CLUSTER_MASTER, 	//ZLEXCOUNT key min max
        'zrange'                => self::CLUSTER_SLAVE, 	//ZRANGE key start stop [WITHSCORES]
        'zrangebylex'           => self::CLUSTER_SLAVE, 	//ZRANGEBYLEX key min max [LIMIT offset count]
        'zrangebyscore'         => self::CLUSTER_SLAVE, 	//ZRANGEBYSCORE key min max [WITHSCORES] [LIMIT offset count]
        'zrank'                 => self::CLUSTER_MASTER, 	//ZRANK key member
        'zrem'                  => self::CLUSTER_MASTER, 	//ZREM key member [member ...]
        'zremrangebylex'        => self::CLUSTER_MASTER, 	//ZREMRANGEBYLEX key min max
        'zremrangebyrank'       => self::CLUSTER_MASTER, 	//ZREMRANGEBYRANK key start stop
        'zremrangebyscore'      => self::CLUSTER_MASTER, 	//ZREMRANGEBYSCORE key min max
        'zrevrange'             => self::CLUSTER_SLAVE, 	//ZREVRANGE key start stop [WITHSCORES]
        'zrevrangebyscore'      => self::CLUSTER_SLAVE, 	//ZREVRANGEBYSCORE key max min [WITHSCORES] [LIMIT offset count]
        'zrevrank'              => self::CLUSTER_SLAVE, 	//ZREVRANK key member
        'zscore'                => self::CLUSTER_SLAVE, 	//ZSCORE key member
        'zunionstore'           => self::CLUSTER_MASTER, 	//ZUNIONSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM MIN MAX]
        'zscan'                 => self::CLUSTER_MASTER, 	//ZSCAN key cursor [MATCH pattern] [COUNT count]

        /* hyperloglog command */
        'pfadd'                 => self::CLUSTER_MASTER, 	//PFADD key element [element ...]
        'pfcount'               => self::CLUSTER_MASTER, 	//PFCOUNT key [key ...]
        'pfmerge'               => self::CLUSTER_MASTER, 	//PFMERGE destkey sourcekey [sourcekey ...]

        /* scripting command */
        'eval'                  => self::CLUSTER_MASTER, 	//EVAL script numkeys key [key ...] arg [arg ...]
        'evalsha'               => self::CLUSTER_MASTER, 	//EVALSHA sha1 numkeys key [key ...] arg [arg ...]

        /* id generator command*/
        'getid'                 => self::CLUSTER_MASTER, 	//getid
        'mgetid'                => self::CLUSTER_MASTER, 	//mgetid count

        /* finite sorted set */
        'xadd'                  => self::CLUSTER_MASTER, 	//xadd
        'xincrby'               => self::CLUSTER_MASTER, 	//xincrby
        'xrange'                => self::CLUSTER_SLAVE, 	//xrange
        'xrevrange'             => self::CLUSTER_SLAVE, 	//xrevrange
        'xscore'                => self::CLUSTER_SLAVE, 	//xscore
        'xrem'                  => self::CLUSTER_MASTER, 	//xrem
        'xcard'                 => self::CLUSTER_SLAVE, 	//xcard
        'xsetoptions'           => self::CLUSTER_MASTER, 	//xsetoptions
        'xgetfinity'            => self::CLUSTER_MASTER, 	//xgetfinity
        'xgetpruning'           => self::CLUSTER_MASTER, 	//xgetpruning
        'xrangebyscore'         => self::CLUSTER_SLAVE, 	//xrangebyscore
        'xrevrangebyscore'      => self::CLUSTER_SLAVE, 	//xrevrangebyscore
        'xrangebylex'           => self::CLUSTER_SLAVE, 	//xrangebylex
        'xrevrangebylex'        => self::CLUSTER_SLAVE, 	//xrevrangebylex
        'xrank'                 => self::CLUSTER_MASTER, 	//xrank
        'xrevrank'              => self::CLUSTER_MASTER, 	//xrevrank
        'xcount'                => self::CLUSTER_MASTER, 	//xcount
        'xlexcount'             => self::CLUSTER_MASTER, 	//xlexcount
        'xremrangebyscore'      => self::CLUSTER_MASTER, 	//xremrangebyscore
        'xremrangebyrank'       => self::CLUSTER_MASTER, 	//xremrangebyrank
        'xremrangebylex'        => self::CLUSTER_MASTER, 	//xremrangebylex
        'xscan'                 => self::CLUSTER_MASTER, 	//xscan

        /* ordered set */
        'oadd'                  => self::CLUSTER_MASTER, 	//oadd
        'ogetmaxlen'            => self::CLUSTER_MASTER, 	//ogetmaxlen
        'ocard'                 => self::CLUSTER_SLAVE, 	//ocard
        'orem'                  => self::CLUSTER_MASTER, 	//orem
        'oremrangebyrank'       => self::CLUSTER_MASTER, 	//oremrangebyrank
        'orange'                => self::CLUSTER_SLAVE, 	//orange
        'orevrange'             => self::CLUSTER_SLAVE, 	//orevrange
        'orangebymember'        => self::CLUSTER_SLAVE, 	//orangebymember
        'orevrangebymember'     => self::CLUSTER_SLAVE, 	//orevrangebymember
    );

}
