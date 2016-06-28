<?php
namespace Ice\Frame\Daemon;
abstract class UnitTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        // 初始化Runner
        if (!isset(\F_Ice::$ins)) {
            $calledClassIns = new \ReflectionClass(get_called_class());
            $calledClassFpath = $calledClassIns->getFileName();
            $pathPosition     = strrpos($calledClassFpath, '/test/');
            $projectRootPath  = substr($calledClassFpath, 0, $pathPosition);
            $runner = new \Ice\Frame\Runner\Daemon($projectRootPath . '/src/conf/app.php');
            $runner->run('ut');
        }

        if (\F_Ice::$ins->runner->name != 'daemon') {
            throw new \Exception('In one UnitTest process, don\'t allow multi runner');
        }
    }

    protected function callDaemon($class, $action) {
        return \F_Ice::$ins->runner->callDaemon($class, $action);
    }

    protected function setUp() {
        echo '[Testing in DaemonRunner] ' . get_called_class() . ':' . $this->getName() . "\n";
    }
}
