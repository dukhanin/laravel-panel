<?php

namespace Dukhanin\Panel\Http\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Traits\PanelListTrait;

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


    protected function urlBuilderToLocalAction($action, $params = null)
    {
        return urlbuilder(method_exists($this, $action) ? action('\\' . get_called_class() . '@' . $action,
            $params) : '\\' . $action);
    }

}