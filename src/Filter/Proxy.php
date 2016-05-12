<?php
namespace Ice\Filter;
class Proxy {
    protected $config;

    protected $filterNamespace;

    protected $compiler;

    protected function __construct() {
        $this->compiler = new Compiler();
    }

    public static function buildForApp($app) {
        $config = $app->config->get('app.runner.filter');

        if ($config) {
            $proxy = new self();
            $proxy->config = config;
            $proxy->filterNamespace = $app->config->get('app.namespace');
        } else {
            $proxy = new \U_Stub();
        }

        return $proxy;
    }

    public function get($code, $strictMode = FALSE) {
        $uniqkey        = md5($srcCode);
        $compilePath    = $this->config['compile_path'];
        $syntaxVersion  = Compiler::SYNTAX_VERSION;
        $proxyClassName = "\\{$this->filterNamespace}\\__FILTER_{$uniqkey}";
        $baseFilterClassName = isset($this->config['base_filter']) ? $this->config['base_filter'] : '\\Ice\\Filter\\Filter';

        $targetFname = "{$compilePath}/{$syntaxVersion}/{$uniqkey}.php";

        if (!is_file($targetFname)) {
            $dstCode = $this->compiler->compile($srcCode, $proxyClassName, $baseFilterClassName);
            if ($dstCode === FALSE) {
                return new \U_Stub();
            }

            @umask(0);
            if (!is_dir(dirname($targetFname))) {
                @mkdir(dirname($targetFname), 0777, TRUE);
            }

            file_put_contents($targetFname, $dstCode);
            @chmod($targetFname, 0777);
        }
        require_once $targetFileName;

        return new $proxyClassName($this->config, $strictMode);
    }
}