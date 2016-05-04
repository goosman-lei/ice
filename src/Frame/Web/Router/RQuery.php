<?php
namespace Ice\Frame\Web\Router;
class RQuery {
    public function route($request, $response) {
        $controller = $request->getQuery('c');
        $action     = $request->getQuery('a');

        $request->controller = ucfirst(strtolower(isset($controller) ? $controller : \F_Ice::$ins->mainAppConf['default_controller']));
        $request->action     = ucfirst(strtolower(isset($action) ? $action : \F_Ice::$ins->mainAppConf['default_action']));

        $response->controller = $request->controller;
        $response->action     = $request->action;

        return TRUE;
    }
}
