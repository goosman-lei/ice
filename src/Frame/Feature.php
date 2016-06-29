<?php
namespace Ice\Frame;
class Feature {
    protected $features = array();

    public function enable($featureName) {
        $this->features[$featureName] = TRUE;
    }

    public function disable($featureName) {
        $this->features[$featureName] = FALSE;
    }

    public function isEnable($featureName) {
        return isset($this->features[$featureName]) && $this->features[$featureName] === TRUE;
    }

    public function __construct($env) {
        $config  = \F_Ice::$ins->mainApp->config->get('feature.config');
        $request = \F_Ice::$ins->runner->request;

        $validFeatures = array();
        if (isset($config['*'])) {
            $validFeatures = array_merge($validFeatures, $config['*']);
        }

        $lowerClass  = $request->class;
        $lowerAction = $request->action;
        $uri         = strtolower("/{$lowerClass}/{$lowerAction}");
        if (isset($config[$uri])) {
            $validFeatures = array_merge($validFeatures, $config[$uri]);
        }

        foreach ($validFeatures as $featureName => $featureDesc) {
            if ($env->isMatch($featureDesc)) {
                $this->enable($featureName);
            }
        }
    }
} 
