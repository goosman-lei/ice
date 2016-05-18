<?php
namespace Ice\Frame\Web\Router;
class RQuery {
    public function route($request, $response) {
        $class  = $request->getQuery('c');
        $action = $request->getQuery('a');

        $request->class  = ucfirst(strtolower(isset($class) ? $class : \F_Ice::$ins->runner->mainAppConf['runner']['frame']['default_class']));
        $request->action = ucfirst(strtolower(isset($action) ? $action : \F_Ice::$ins->runner->mainAppConf['runner']['frame']['default_action']));

        $response->class  = $request->class;
        $response->action = $request->action;

        return TRUE;
    }
}
