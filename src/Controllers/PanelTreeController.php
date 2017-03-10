<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Controllers;
use Dukhanin\Panel\Traits\PanelTreeTrait;

abstract class PanelTreeController extends Controller
{

    use PanelTreeTrait;


    public function initUrl()
    {
        if ($route = app('router')->getRoutes()->getByAction($action = get_class($this) . '@showList')) {
            $this->url = app('url')->action('\\' . $action, app('router')->current()->parameters());
        }
    }


    public function callAction($action, $parameters)
    {
        $this->init();

        if (method_exists($this, 'before')) {
            $this->before();
        }

        $res = call_user_func_array([ $this, $action ], $parameters);

        if (method_exists($this, 'after')) {
            $this->after();
        }

        return $res;
    }


    public function method($checkActions = null)
    {
        if ( ! ( $route = app('router')->current() )) {
            return false;
        }

        $methodName = Str::parseCallback($route->getActionName())[1];

        if (is_null($checkActions)) {
            return $methodName;
        }

        return in_array(strtolower($methodName), array_map('strtolower', (array) $checkActions));
    }


    protected function urlBuilderToLocalAction($action, $params = null)
    {
        return urlbuilder(method_exists($this, $action) ? action('\\' . get_called_class() . '@' . $action,
            $params) : $action);
    }

}