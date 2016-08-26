<?php
namespace Ice\Resource\Handler;
class Redis extends Abs {
    /**
     * key
     * 获取redis的key的统一接口, 行为和sprintf一致
     * @param mixed $fmt
     * @static
     * @access public
     * @return string
     */
    public static function key($fmt) {
        $argv = func_get_args();
        return call_user_func_array('sprintf', $argv);
    }

    public function __call($method, $parameters) {
        // 命令合法性检查
        $method = strtolower($method);
        if (!isset($this->allow_cmds[$method])) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'host'    => $this->nodeConfig['host'],
                'port'    => $this->nodeConfig['port'],
                'command' => $method,
            ), \F_ECode::REDIS_FOBIDDEN_COMMAND);
            return FALSE;
        }

        try {
            $resp = call_user_func_array(array($this->conn, $method), $parameters);
        } catch (\RedisException $e) {
            \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                'host'    => $this->nodeConfig['host'],
                'port'    => $this->nodeConfig['port'],
                'command' => $method,
            ), \F_ECode::REDIS_COMMAND_ERROR);
            $resp = FALSE;
        }
        return $resp;
    }

    /**
     * allow_cmds
     * proxy允许的所有redis命令
     * @var array
     * @access private
     */
    private $allow_cmds = array(
        /* keys command */
        'del'                   => 'DEL key [key …]',
        'dump'                  => 'DUMP key',
        'exists'                => 'EXISTS key',
        'expire'                => 'EXPIRE key seconds',
        'expireat'              => 'EXPIREAT key timestamp',
        'persist'               => 'PERSIST key',
        'pexpire'               => 'PEXPIRE key milliseconds',
        'pexpireat'             => 'PEXPIREAT key milliseconds-timestamp',
        'pttl'                  => 'PTTL key',
        'restore'               => 'RESTORE key ttl serialized-value',
        'sort'                  => 'SORT key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC DESC] [ALPHA] [STORE destination]',
        'ttl'                   => 'TTL key',
        'type'                  => 'TYPE key',

        /* strings command */
        'append'                => 'APPEND key value',
        'bitcount'              => 'BITCOUNT key [start] [end]',
        'decr'                  => 'DECR key',
        'decrby'                => 'DECRBY key decrement',
        'get'                   => 'GET key',
        'getbit'                => 'GETBIT key offset',
        'getrange'              => 'GETRANGE key start end',
        'getset'                => 'GETSET key value',
        'incr'                  => 'INCR key',
        'incrby'                => 'INCRBY key increment',
        'incrbyfloat'           => 'INCRBYFLOAT key increment',
        'mget'                  => 'MGET key [key ...]',
        'mset'                  => 'MSET key value [key value ...]',
        'psetex'                => 'PSETEX key milliseconds value',
        'set'                   => 'SET key value [EX seconds] [PX milliseconds] [NX XX]',
        'setbit'                => 'SETBIT key offset value',
        'setex'                 => 'SETEX key seconds value',
        'setnx'                 => 'SETNX key value',
        'setrange'              => 'SETRANGE key offset value',
        'strlen'                => 'STRLEN key',

        /* hashes command */
        'hdel'                  => 'HDEL key field [field ...]',
        'hexists'               => 'HEXISTS key field',
        'hget'                  => 'HGET key field',
        'hgetall'               => 'HGETALL key',
        'hincrby'               => 'HINCRBY key field increment',
        'hincrbyfloat'          => 'HINCRBYFLOAT key field increment',
        'hkeys'                 => 'HKEYS key',
        'hlen'                  => 'HLEN key',
        'hmget'                 => 'HMGET key field [field ...]',
        'hmset'                 => 'HMSET key field value [field value ...]',
        'hset'                  => 'HSET key field value',
        'hsetnx'                => 'HSETNX key field value',
        'hvals'                 => 'HVALS key',
        'hscan'                 => 'HSCAN key cursor [MATCH pattern] [COUNT count]',

        /* lists command */
        'lindex'                => 'LINDEX key index',
        'linsert'               => 'LINSERT key BEFORE AFTER pivot value',
        'llen'                  => 'LLEN key',
        'lpop'                  => 'LPOP key',
        'lpush'                 => 'LPUSH key value [value ...]',
        'lpushx'                => 'LPUSHX key value',
        'lrange'                => 'LRANGE key start stop',
        'lrem'                  => 'LREM key count value',
        'lset'                  => 'LSET key index value',
        'ltrim'                 => 'LTRIM key start stop',
        'rpop'                  => 'RPOP key',
        'rpoplpush'             => 'RPOPLPUSH source destination',
        'rpush'                 => 'RPUSH key value [value ...]',
        'rpushx'                => 'RPUSHX key value',

        /* sets command */
        'sadd'                  => 'SADD key member [member ...]',
        'scard'                 => 'SCARD key',
        'sdiff'                 => 'SDIFF key [key ...]',
        'sdiffstore'            => 'SDIFFSTORE destination key [key ...]',
        'sinter'                => 'SINTER key [key ...]',
        'sinterstore'           => 'SINTERSTORE destination key [key ...]',
        'sismember'             => 'SISMEMBER key member',
        'smembers'              => 'SMEMBERS key',
        'smove'                 => 'SMOVE source destination member',
        'spop'                  => 'SPOP key',
        'srandmember'           => 'SRANDMEMBER key [count]',
        'srem'                  => 'SREM key member [member ...]',
        'sunion'                => 'SUNION key [key ...]',
        'sunionstore'           => 'SUNIONSTORE destination key [key ...]',
        'sscan'                 => 'SSCAN key cursor [MATCH pattern] [COUNT count]',

        /* sorted sets command */
        'zadd'                  => 'ZADD key score member [score] [member]',
        'zcard'                 => 'ZCARD key',
        'zcount'                => 'ZCOUNT key min max',
        'zincrby'               => 'ZINCRBY key increment member',
        'zinterstore'           => 'ZINTERSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM MIN MAX]',
        'zlexcount'             => 'ZLEXCOUNT key min max',
        'zrange'                => 'ZRANGE key start stop [WITHSCORES]',
        'zrangebylex'           => 'ZRANGEBYLEX key min max [LIMIT offset count]',
        'zrangebyscore'         => 'ZRANGEBYSCORE key min max [WITHSCORES] [LIMIT offset count]',
        'zrank'                 => 'ZRANK key member',
        'zrem'                  => 'ZREM key member [member ...]',
        'zremrangebylex'        => 'ZREMRANGEBYLEX key min max',
        'zremrangebyrank'       => 'ZREMRANGEBYRANK key start stop',
        'zremrangebyscore'      => 'ZREMRANGEBYSCORE key min max',
        'zrevrange'             => 'ZREVRANGE key start stop [WITHSCORES]',
        'zrevrangebyscore'      => 'ZREVRANGEBYSCORE key max min [WITHSCORES] [LIMIT offset count]',
        'zrevrank'              => 'ZREVRANK key member',
        'zscore'                => 'ZSCORE key member',
        'zunionstore'           => 'ZUNIONSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM MIN MAX]',
        'zscan'                 => 'ZSCAN key cursor [MATCH pattern] [COUNT count]',

        /* hyperloglog command */
        'pfadd'                 => 'PFADD key element [element ...]',
        'pfcount'               => 'PFCOUNT key [key ...]',
        'pfmerge'               => 'PFMERGE destkey sourcekey [sourcekey ...]',

        /* scripting command */
        'eval'                  => 'EVAL script numkeys key [key ...] arg [arg ...]',
        'evalsha'               => 'EVALSHA sha1 numkeys key [key ...] arg [arg ...]',

        /* id generator command*/
        'getid'                 => 'getid',
        'mgetid'                => 'mgetid count',

        /* finite sorted set */
        'xadd'                  => 'xadd',
        'xincrby'               => 'xincrby',
        'xrange'                => 'xrange',
        'xrevrange'             => 'xrevrange',
        'xscore'                => 'xscore',
        'xrem'                  => 'xrem',
        'xcard'                 => 'xcard',
        'xsetoptions'           => 'xsetoptions',
        'xgetfinity'            => 'xgetfinity',
        'xgetpruning'           => 'xgetpruning',
        'xrangebyscore'         => 'xrangebyscore',
        'xrevrangebyscore'      => 'xrevrangebyscore',
        'xrangebylex'           => 'xrangebylex',
        'xrevrangebylex'        => 'xrevrangebylex',
        'xrank'                 => 'xrank',
        'xrevrank'              => 'xrevrank',
        'xcount'                => 'xcount',
        'xlexcount'             => 'xlexcount',
        'xremrangebyscore'      => 'xremrangebyscore',
        'xremrangebyrank'       => 'xremrangebyrank',
        'xremrangebylex'        => 'xremrangebylex',
        'xscan'                 => 'xscan',

        /* ordered set */
        'oadd'                  => 'oadd',
        'ogetmaxlen'            => 'ogetmaxlen',
        'ocard'                 => 'ocard',
        'orem'                  => 'orem',
        'oremrangebyrank'       => 'oremrangebyrank',
        'orange'                => 'orange',
        'orevrange'             => 'orevrange',
        'orangebymember'        => 'orangebymember',
        'orevrangebymember'     => 'orevrangebymember',
    );

}
