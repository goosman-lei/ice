<?php
namespace Ice\Filter;
class Proxy {
    protected $config;
    protected $opClass;

    public function __construct($config) {
        $this->config  = $config;
        $this->opClass = isset($this->config['op_class']) ? $this->config['op_class'] : '\\Ice\\Filter\\Op';
    }

    public function get($code) {
        // TODO 预处理阶段

        $uniqkey = md5($srcCode);

        $targetClassName = '__FILTER_' . $uniqkey;
        $compilePath     = \F_Ice::$ins->mainApp->config->rtrim($this->config['compile_path'], '/');
        $syntaxVersion   = self::SYNTAX_VERSION;
        $targetFileName  = "${compilePath}/${syntaxVersion}/${uniqkey}.php";

        if (!is_file($targetFileName)) {
            try {
                $objectCode = Compiler::compile($srcCode, $targetClassName);
            } catch (CompileException $e) {
                $this->warn($e->getMessage(), self::CKCR_COMPILE_ERROR);
                return FALSE;
            }

            if (!is_dir(dirname($targetFileName))) {
                @umask(0);
                @mkdir(dirname($targetFileName), 0777, TRUE);
            }
            file_put_contents($targetFileName, $objectCode);
            @chmod($targetFileName, 0777);
        }
        require_once $targetFileName;

        return new $targetClassName($this->config);

    }
}