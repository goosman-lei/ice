<?php
namespace Ice\Frame;
class Ice {
    // static info
    public $rootPath;

    // context
    public $mainApp;
    public $workApp;
    public $runner;

    // handler
    public $logger;
    public $errorHandler;

    public static $ins;

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
    }

    public function setup($runner) {
        self::$ins = $this;
        $this->runner = $runner;

        // setup application
        // no class exists check. you must guarantee that by yourself pre online
        $mainAppNamespace = $this->runner->mainAppConf['app_class'];
        $mainAppClass     = $this->runner->mainAppConf['app_class'];
        $mainApp          = new $mainAppClass($rootPath);
        $this->mainApp    = \F_App::registerApp($mainAppNamespace, $mainApp);
        $this->workApp    = $this->mainApp;

        // setup logger
        $this->logger = $this->mainApp->logger_common;

        // setup errorhandler
        $this->errorHandler = new \Ice\Frame\Error\Handler();
    }
}
