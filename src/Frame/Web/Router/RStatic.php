<?php
namespace Ice\Frame\Web\Router;
class RStatic {
    public function route($request, $response) {
        preg_match(';^/(\w+)?(?:/(\w+)?)?;', $request->uri, $match);

        $request->class  = ucfirst(strtolower(isset($match[1]) ? $match[1] : \F_Ice::$ins->runner->mainAppConf['runner']['frame']['default_class']));
        $request->action = ucfirst(strtolower(isset($match[2]) ? $match[2] : \F_Ice::$ins->runner->mainAppConf['runner']['frame']['default_action']));

        $response->class  = $request->class;
        $response->action = $request->action;

        return TRUE;
    }
}
