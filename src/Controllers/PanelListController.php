<?php

namespace Dukhanin\Panel\Controllers;

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


    protected function urlBuilderToLocalAction($action, $params = null)
    {
        return urlbuilder(method_exists($this, $action) ? action('\\' . get_called_class() . '@' . $action,
            $params) : '\\' . $action);
    }

}