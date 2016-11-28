<?php
namespace Ice\Frame\Web;
abstract class UnitTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        // 初始化Runner
        if (!isset(\F_Ice::$ins)) {
            $calledClassIns = new \ReflectionClass(get_called_class());
            $calledClassFpath = $calledClassIns->getFileName();
            $pathPosition     = strrpos($calledClassFpath, '/test/');
            $projectRootPath  = substr($calledClassFpath, 0, $pathPosition);
            $runner = new \Ice\Frame\Runner\Web($projectRootPath . '/src/conf/app.php');
            $runner->run('ut');
        }
    }

    protected function callAction($class, $action) {
        return \F_Ice::$ins->runner->callAction($class, $action);
    }

    protected function setUp() {
        echo '[Testing in WebRunner][pid:' . posix_getpid() . '] ' . get_called_class() . ':' . $this->getName() . "\n";
    }
}
