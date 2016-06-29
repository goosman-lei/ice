<?php
namespace Ice\Message;
class Factory {
    protected $config;

    public static function factory($class, $action, $params) {
        $lowerClass  = strtolower($class);
        $lowerAction = strtolower($action);
        $config     = \F_Ice::$ins->workApp->config->get("message.{$lowerClass}.{$lowerAction}");
        if (empty($config) || !is_array($config)) {
            return new \U_Stub;
        }

        $messageClass = isset($config['class'])
                    ? $config['class']
                    : \F_Ice::$ins->workApp->config->get("message.default_class");
        if (empty($messageClass) || !class_exists($messageClass) || !is_subclass_of($messageClass, "\\Ice\\Message\\Abs")) {
            return new \U_Stub;
        }

        $messageConfig = isset($config['config']) ? $config['config'] : array();

        $message = new $messageClass($class, $action, $params, $messageConfig);

        $message->setPublishMode(isset($config['mode']) ? $config['mode'] : 'full');

        $message->addExtra();

        $message->createId();

        return $message;
    }

    public static function unserialize($message, $runMode) {
        $data = json_decode($message, TRUE);
        if (empty($data)) {
            return new \U_Stub;
        }

        if (!isset($data['class']) || !isset($data['action'])) {
            return new \U_Stub;
        }

        $class  = $data['class'];
        $action = $data['action'];

        $lowerClass  = strtolower($class);
        $lowerAction = strtolower($action);
        $config     = \F_Ice::$ins->workApp->config->get("message.{$lowerClass}.{$lowerAction}");
        if (empty($config) || !is_array($config)) {
            return new \U_Stub;
        }

        $messageClass = isset($config['class'])
                    ? $config['class']
                    : \F_Ice::$ins->workApp->config->get("message.default_class");
        if (empty($messageClass) || !class_exists($messageClass) || !is_subclass_of($messageClass, "\\Ice\\Message\\Abs")) {
            return new \U_Stub;
        }

        $messageConfig = isset($config['config']) ? $config['config'] : array();

        $messageObj = new $messageClass($class, $action, @$data['params'], $messageConfig);
        $messageObj->id       = @$data['id'];
        $messageObj->runMode  = $runMode == 'master' ? Abs::MODE_MASTER : Abs::MODE_SLAVE;
        $messageObj->extra    = isset($data['extra']) ? $data['extra'] : array();
        $messageObj->setPublishMode('none');

        return $messageObj;

    }
}
