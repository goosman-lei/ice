<?php
namespace Ice\Resource;
class Proxy {

    protected static $_staticConnArr = array();

    protected $_confArr = array();

    protected $_handlerArr = array();
    protected $_mapping = array();

    protected function __construct() {
    }

    protected function initConf() {
        if (is_array($this->_pool) && !empty($this->_pool)) {
            foreach ($this->_pool as $scheme => $schemeConfig) {
                $schemeOptions = isset($schemeConfig['options']) ? (array)$schemeConfig['options'] : array();
                unset($schemeConfig['options']);
                $connectorClass = isset($this->_mapping['connector'][$scheme])
                    ? $this->_mapping['connector'][$scheme]
                    : '\\Ice\\Resource\\Connector\\' . ucfirst(strtolower($scheme));
                if (!class_exists($connectorClass)) {
                    \F_Ice::$ins->mainApp->logger_comm->warn(array(
                        'scheme' => $scheme,
                        'class'  => $connectorClass,
                    ), \F_ECode::R_NO_CONNECTOR);
                }
                if (!isset($this->_confArr[$scheme])) {
                    $this->_confArr[$scheme] = array();
                }

                foreach ($schemeConfig as $unitname => $unitConfig) {
                    $unitOptions = isset($unitConfig['options']) ? (array)$unitConfig['options'] : array();
                    unset($unitConfig['options']);
                    if (!isset($this->_confArr[$scheme][$unitname])) {
                        $this->_confArr[$scheme][$unitname] = array();
                    }

                    foreach ($unitConfig as $cluster => $clusterConfig) {
                        $clusterOptions = isset($clusterConfig['options']) ? (array)$clusterConfig['options'] : array();
                        unset($clusterConfig['options']);
                        if (!isset($this->_confArr[$scheme][$unitname][$cluster])) {
                            $this->_confArr[$scheme][$unitname][$cluster] = array();
                        }

                        foreach ($clusterConfig as $node => $nodeConfig) {
                            $nodeOptions = isset($nodeConfig['options']) ? (array)$nodeConfig['options'] : array();
                            unset($nodeConfig['options']);
                            $nodeOptions = array_merge($schemeOptions, $unitOptions, $clusterOptions, $nodeOptions);
                            list($nodeConfig, $nodeOptions) = call_user_func(array($connectorClass, 'mergeDefault'), $nodeConfig, $nodeOptions);

                            $nodeSn = call_user_func(array($connectorClass, 'getSn'), $nodeConfig, $nodeOptions);

                            $this->_confArr[$scheme][$unitname][$cluster][$nodeSn] = array(
                                'config'   => $nodeConfig,
                                'options'  => $nodeOptions,
                                'class'    => $connectorClass,
                                'scheme'   => $scheme,
                                'unitname' => $unitname,
                                'cluster'  => $cluster,
                            );
                        }
                    }
                }
            }
        }
    }

    public static function buildForApp($app) {
        $proxy = new self();

        $proxy->_mapping = $app->config->get('resource.mapping') ?: array();
        $proxy->_pool    = $app->config->get('resource.pool') ?: array();

        $proxy->initConf();

        return $proxy;
    }

    public function get($uri) {
        // <schme> "://" <unitname> "/" <cluster> [ "?algo=random&force_new=1" ]
        $isMatch = preg_match(';^
            (?P<scheme>[\w-]++)
            ://
            (?P<unitname>[\w\.-]++)
            (?:/(?P<cluster>[\w-]++))?
            (?:\?(?P<params>.*+))?
        ;x', $uri, $match);

        if (!$isMatch) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'uri'  => $uri,
            ), \F_ECode::R_ERROR_URI);
            return new \U_Stub();
        }

        $scheme   = @$match['scheme'];
        $unitname = @$match['unitname'];
        $cluster  = @empty($match['cluster']) ? 'default' : $match['cluster'];
        $strategy = 'random';
        $forceNew = FALSE;
        if (!empty($match['params'])) {
            $params = array();
            parse_str($match['params'], $params);
            $strategy = isset($params['algo']) ? $params['algo'] : $strategy;
            $forceNew = (bool)(isset($params['force_new']) ? $params['force_new'] : $forceNew);
        }

        $uriSn = sprintf("%s://%s/%s", $scheme, $unitname, $cluster);
        if (isset($this->_handlerArr[$uriSn]) && !$forceNew) {
            return $this->_handlerArr[$uriSn];
        }

        if (!isset($this->_confArr[$scheme][$unitname][$cluster]) || empty($this->_confArr[$scheme][$unitname][$cluster])) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'uri'  => $uri,
            ), \F_ECode::R_ERROR_URI);
            return new \U_Stub();
        }
        $nodeInfos = $this->_confArr[$scheme][$unitname][$cluster];


        $strategyClass = isset($this->_mapping['strategy'][$strategy])
            ? $this->_mapping['strategy'][$strategy]
            : '\\Ice\\Resource\\Strategy\\' . ucfirst(strtolower($strategy));
        if (!class_exists($strategyClass)) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'strategy' => $strategy,
                'class'    => $strategyClass,
            ), \F_ECode::R_NO_STRATEGY);
            return new \U_Stub();
        }
        $conn = FALSE;
        do {
            $nodeSn = call_user_func(array($strategyClass, 'getNode'), $nodeInfos);
            if ($nodeSn === FALSE) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'uri'  => $uri,
                ), \F_ECode::R_ERROR_GET_NODE);
                return new \U_Stub();
            }
            $nodeInfo = $nodeInfos[$nodeSn];
            $nodeInfo['uri'] = $uri;
            $nodeInfo['sn']  = $nodeSn;

            $conn = $this->getRealConn($nodeSn, $nodeInfo, $forceNew);
            if (!isset($conn) || $conn === FALSE) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'uri'    => $uri,
                    'nodeSn' => $nodeSn,
                ), \F_ECode::R_ERROR_GET_CONN);
                unset($nodeInfos[$nodeSn]);
            }
        } while (!empty($nodeInfos) && $conn === FALSE);

        if ($conn === FALSE) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'uri'    => $uri,
            ), \F_ECode::R_ERROR_GET_ALL_CONN);
            return new \U_Stub();
        }

        $handlerClass = isset($this->_mapping['handler'][$scheme])
            ? $this->_mapping['handler'][$scheme]
            : '\\Ice\\Resource\\Handler\\' . ucfirst(strtolower($scheme));
        if (!class_exists($handlerClass)) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'scheme' => $scheme,
                'class'  => $handlerClass,
            ), \F_ECode::R_NO_HANDLER);
            return new \U_Stub();
        }
        $handler = new $handlerClass();
        $handler->setConn($conn);
        $handler->setNodeInfo($nodeInfo);
        $handler->setProxy($this);

        if (!isset($this->_handlerArr[$uriSn])) {
            $this->_handlerArr[$uriSn] = $handler;
        }

        return $handler;
    }

    public function getRealConn($nodeSn, $nodeInfo, $forceNew = FALSE) {
        $connectorClass = $nodeInfo['class'];
        $nodeConfig     = $nodeInfo['config'];
        $nodeOptions    = $nodeInfo['options'];
        $scheme   = $nodeInfo['scheme'];
        $cacheSn  = "$scheme:$nodeSn";

        if (isset(self::$_staticConnArr[$cacheSn]) && !$forceNew) {
            return self::$_staticConnArr[$cacheSn];
        }

        $conn = @call_user_func(array($connectorClass, 'getConn'), $nodeInfo);
        if (!isset($conn) || $conn === FALSE) {
            return FALSE;
        }

        if (!isset(self::$_staticConnArr[$cacheSn])) {
            self::$_staticConnArr[$cacheSn] = $conn;
        }

        return $conn;
    }
}
