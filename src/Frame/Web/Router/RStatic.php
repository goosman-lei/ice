<?php
namespace Ice\Frame\Web\Router;
class RStatic {
    public function route($request, $response) {
        preg_match(';^/(\w+)?(?:/(\w+)?)?;', $request->uri, $match);

        $request->controller = ucfirst(strtolower(isset($match[1]) ? $match[1] : \F_Ice::$ins->mainAppConf['default_controller']));
        $request->action     = ucfirst(strtolower(isset($match[2]) ? $match[2] : \F_Ice::$ins->mainAppConf['default_action']));

        $response->controller = $request->controller;
        $response->action     = $request->action;

        return TRUE;
    }
}
