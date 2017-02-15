<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Controllers;
use Dukhanin\Panel\Traits\PanelListTrait;
use Illuminate\Support\Str;

abstract class PanelListController extends Controller
{

    use PanelListTrait;


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
}