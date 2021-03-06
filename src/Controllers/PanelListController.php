<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Dukhanin\Panel\Collections\ActionsCollection;
use Dukhanin\Panel\Collections\ColumnsCollection;
use Dukhanin\Panel\PanelListDecorator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Dukhanin\Panel\Traits\HasAssets;
use Dukhanin\Panel\Traits\HasConfig;

abstract class PanelListController extends Controller
{
    use HasConfig, HasAssets, Concerns\ChecksAccess, Concerns\SetsRoutes;

    protected $url;

    protected $urlParameters;

    protected $model;

    protected $label;

    protected $query;

    protected $view;

    protected $layout;

    protected $decorator;

    protected $columns;

    protected $actions;

    protected $modelActions;

    protected $groupActions;

    public function __construct()
    {
        $this->actions = new ActionsCollection;
        $this->actions->setPanel($this);

        $this->modelActions = new ActionsCollection;
        $this->modelActions->setPanel($this);

        $this->groupActions = new ActionsCollection;
        $this->groupActions->setPanel($this);

        $this->columns = new ColumnsCollection;
        $this->columns->setPanel($this);
    }

    public function callAction($action, $parameters)
    {
        $this->init();

        if (method_exists($this, 'before')) {
            $this->before();
        }

        $res = call_user_func_array([$this, $action], $parameters);

        if (method_exists($this, 'after')) {
            $this->after();
        }

        return $res;
    }

    abstract public function initModel();

    public function showList()
    {
        return $this->view();
    }

    public function init()
    {
        $this->initActions();

        $this->initModelActions();

        $this->initGroupActions();

        $this->initColumns();

        $this->initFeatures();
    }

    public function initFeatures()
    {
        foreach (class_uses_recursive(get_class($this)) as $trait) {
            $method = 'initFeature'.class_basename($trait);

            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    public function initLabel()
    {
        $this->label = '';
    }

    public function initQuery()
    {
        $this->query = $this->model()->newQuery();
    }


    public function initView()
    {
        $this->view = view($this->config('views').'.list', ['panel' => $this->decorator()]);
    }

    public function initDecorator()
    {
        $this->decorator = new PanelListDecorator($this);
    }

    public function initColumns()
    {
        $this->columns->put('name', [
            'label' => app('translator')->trans('panel.labels.name'),
            'order' => true,
            'action' => true,
            'handler' => function ($model, &$cell, &$row) {
                return $model->name;
            },
        ]);
    }

    public function initActions()
    {
    }

    public function initModelActions()
    {
    }

    public function initGroupActions()
    {
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function url(array $apply = ['*'])
    {
        if (empty($this->url)) {
            $this->initUrl();
        }

        $url = urlbuilder($this->url);

        $this->apply($url, $apply, 'applyUrl');;

        foreach ($apply as $key => $value) {
            if (is_integer($key)) {
                unset($apply[$key]);
            }
        }

        return $url->query($apply)->compile();
    }

    public function initUrlParameters()
    {
        $this->urlParameters = [];

        if ($route = Route::current()) {
            foreach ($route->parameterNames as $name) {
                $this->urlParameters[$name] = array_get($route->parameters, $name);
            }
        }
    }

    public function urlParameters()
    {
        if (is_null($this->urlParameters)) {
            $this->initUrlParameters();
        }

        return $this->urlParameters;
    }

    public function formatUrlParameters($params)
    {
        return url()->formatParameters($params);
    }

    public function urlTo($action, $params = null, array $apply = ['*'])
    {
        if (! str_contains($action, '@')) {
            $action = static::routeAction($action);
        }

        $params = $this->formatUrlParameters($params) + $this->urlParameters();

        $url = urlbuilder(action($action, $params));

        $this->apply($url, $apply, 'applyUrl');

        return $url->compile();
    }

    public function model()
    {
        if (is_null($this->model)) {
            $this->initModel();
        }

        return $this->model;
    }

    public function label()
    {
        if (is_null($this->label)) {
            $this->initLabel();
        }

        return $this->label;
    }

    public function query(array $apply = ['*'])
    {
        if (is_null($this->query)) {
            $this->initQuery();
        }

        $select = clone $this->query;

        $this->apply($select, $apply, 'applyQuery');

        return $select;
    }

    public function items(array $apply = ['*'])
    {
        return $this->query($apply)->get();
    }

    public function total(array $apply = [])
    {
        $apply[] = '!page';

        $builder = $this->query($apply);

        $builder->getQuery()->orders = [];
        $builder->getQuery()->unionOrders = [];

        return $builder->count();
    }

    public function view()
    {
        if (is_null($this->view)) {
            $this->initView();
        }

        return $this->view;
    }

    public function decorator()
    {
        if (is_null($this->decorator)) {
            $this->initDecorator();
        }

        return $this->decorator;
    }

    public function columns()
    {
        if (! $this->columns->touched()) {
            $this->initColumns();
        }

        return $this->columns;
    }

    public function actions()
    {
        if (! $this->actions->touched()) {
            $this->initActions();
        }

        return $this->actions;
    }

    public function modelActions()
    {
        if (! $this->modelActions->touched()) {
            $this->initModelActions();
        }

        return $this->modelActions;
    }

    public function groupActions()
    {
        if (! $this->groupActions->touched()) {
            $this->initGroupActions();
        }

        return $this->groupActions;
    }

    public function input($key = null, $default = null)
    {
        return request()->input($key, $default);
    }

    public function parameter($key, $default = null)
    {
        return request()->route()->parameter($key, $default);
    }

    public function parameters()
    {
        return request()->route()->parameters();
    }

    public function findModel($primaryKey)
    {
        return $this->query(['!page', '!order'])->find($primaryKey);
    }

    public function findModelOrFail($primaryKey)
    {
        if (! $model = $this->findModel($primaryKey)) {
            throw new ModelNotFoundException;
        }

        return $model;
    }

    public function findModels($primaryKeys)
    {
        return $this->query(['!page'])->findMany($primaryKeys);
    }

    public function findModelsOrFail($primaryKeys)
    {
        if (count($collection = $this->findModels($primaryKeys)) == 0) {
            throw new ModelNotFoundException;
        }

        return $collection;
    }


    public function eachRow(&$row, $handlers = ['*'])
    {
        $this->apply($row, $handlers, $methodPrefix = 'eachRow');
    }

    protected function newModel()
    {
        $model = clone $this->model();

        $this->apply($model, ['*'], 'newModel');

        return $model;
    }

    protected function apply(&$object, array $list, $methodPrefix = '')
    {
        if (in_array(false, $list, true)) {
            return;
        }

        $methodPrefix = preg_replace('/^apply/i', '', $methodPrefix);
        $handlers = array_map('strtolower', $list);
        $handlersExclude = [];

        foreach ($handlers as $key => $name) {
            if (! is_integer($key)) {
                unset($handlers[$key]);
            }

            if ($name[0] == '!') {
                $handlersExclude[] = trim($name, '!');
            }
        }

        if ($handlersExclude) {
            $handlers[] = '*';
        }

        if (in_array('*', $handlers)) {
            foreach (get_class_methods($this) as $method) {
                $method = strtolower($method);

                if (! preg_match('/^apply'.preg_quote($methodPrefix).'(.+)$/i', $method, $pock)) {
                    continue;
                }

                if (in_array($pock[1], $handlersExclude, true)) {
                    continue;
                }

                $this->{$method}($object);
            }
        } else {
            foreach ($handlers as $arg) {
                $method = 'apply'.$methodPrefix.$arg;

                if (is_callable([$this, $method])) {
                    $this->{$method}($object);
                }
            }
        }
    }

    public function initUrl()
    {
        $this->url = action(static::routeAction('showList'), $this->urlParameters());
    }
}