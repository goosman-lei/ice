<?php
/**
 * service和action层支持多机房部署.
 * 因此, service和action层可以访问Message->$mode, 据此决定业务分支
 */
namespace Ice\Message;
abstract class Abs {

    const MODE_MASTER = 0x01;
    const MODE_SLAVE  = 0x02;
    const MODE_NONE   = 0x00;
    const MODE_FULL   = 0x03;
    public static $modeMapping = array(
        'master' => self::MODE_MASTER,
        'slave'  => self::MODE_SLAVE,
        'full'   => self::MODE_FULL,
        'none'   => self::MODE_NONE,
    );

    public $runMode = self::MODE_SLAVE;
    public $id;

    public $class;
    public $action;
    public $params;

    public $extra = array();

    public $config;

    public $publishMode = self::MODE_FULL;

    public function __construct($class, $action, $params, $config) {
        $this->class  = $class;
        $this->action = $action;
        $this->params = $params;
        $this->config = $config;
    }

    public function setPublishMode($publishMode) {
        if (array_key_exists($publishMode, self::$modeMapping)) {
            $this->publishMode = self::$modeMapping[$publishMode];
        }
    }

    public function setRunMode($runMode) {
        if ($runMode == 'master') {
            $this->runMode = self::MODE_MASTER;
        }
    }

    public function serialize() {
        return json_encode(array(
            'id'     => $this->id,
            'class'  => $this->class,
            'action' => $this->action,
            'params' => $this->params,
            'extra'  => $this->extra,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * addExtra 
     * 供应用扩展: 添加额外的数据. 比如提前在message层生成下面分布式业务要使用的ID
     * @access public
     * @return void
     */
    public function addExtra() {
    }

    abstract public function createId();

    abstract public function publishMaster();

    abstract public function publishSlave();

    public function complete() {
        if ($this->publishMode & self::MODE_MASTER) {
            $this->publishMaster();
        }
        if ($this->publishMode & self::MODE_SLAVE) {
            $this->publishSlave();
        }
    }

    abstract public function isCompleted();

    public function isMaster() {
        return $this->runMode & self::MODE_MASTER;
    }

    public function isSlave() {
        return $this->runMode & self::MODE_SLAVE;
    }
}