<?php
namespace Ice\Message;
class Factory {
    protected $config;

    public static function factory($class, $action, $params) {
        $config     = \F_Ice::$ins->workApp->config->get("message.{$class}.{$action}");
        if (empty($config) || !is_array($config)) {
            return NULL;
        }

        $messageClass = isset($config['class'])
                    ? $config['class']
                    : \F_Ice::$ins->workApp->config->get("message.default_class");
        if (empty($messageClass) || !class_exists($messageClass) || !($messageClass instanceof \Ice\Message\Abs)) {
            return NULL;
        }

        $message = new $messageClass($class, $action, $params);

        $message->setPushMode(isset($config['mode']) ? $config['mode'] : 'full');

        $message->createData();

        $message->createId();

        if ($message->isMaster()) {
            $message->publishMaster();
        }

        if ($message->isSlave()) {
            $message->publishSlave();
        }

        return $message;
    }

    public static function unserialize($message, $runMode) {
        $data = json_decode($message, TRUE);
        if (empty($data)) {
            return NULL;
        }

        if (!isset($data['class']) || !isset($data['action'])) {
            return NULL;
        }

        $class  = $data['class'];
        $action = $data['action'];

        $config     = \F_Ice::$ins->workApp->config->get("message.{$class}.{$action}");
        if (empty($config) || !is_array($config)) {
            return NULL;
        }

        $messageClass = isset($config['class'])
                    ? $config['class']
                    : \F_Ice::$ins->workApp->config->get("message.default_class");
        if (empty($messageClass) || !class_exists($messageClass) || !($messageClass instanceof \Ice\Message\Abs)) {
            return NULL;
        }


        $messageObj = new $messageClass($class, $action, @$data['params']);
        $messageObj->id       = @$data['id'];
        $messageObj->runMode  = $runMode == 'master' ? Abs::MODE_MASTER : Abs::MODE_SLAVE;
        $messageObj->pushMode = Abs::MODE_NONE;

        return $messageObj;

    }
}
