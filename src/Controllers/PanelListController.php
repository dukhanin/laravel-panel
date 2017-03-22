<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Collections\ActionsCollection;
use Dukhanin\Panel\Collections\ColumnsCollection;
use Dukhanin\Panel\PanelListDecorator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;


abstract class PanelListController extends Controller
{

    protected $url;

    protected $model;

    protected $label;

    protected $query;

    protected $policy;

    protected $view;

    protected $layout;

    protected $decorator;

    protected $columns;

    protected $actions;

    protected $modelActions;

    protected $groupActions;

    protected $config;


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


    public function initConfig()
    {
        $this->config = config('panel');
    }


    public function initFeatures()
    {
        foreach (class_uses_recursive(get_class($this)) as $trait) {
            $method = 'initFeature' . class_basename($trait);

            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }


    public function initModel()
    {

    }


    public function initLabel()
    {
        $this->label = '';
    }


    public function initQuery()
    {
        $this->query = $this->model()->newQuery();
    }


    public function initPolicy()
    {
        $this->policy = Gate::getPolicyFor($this->model());
    }


    public function initView()
    {
        $this->view = view($this->config('views') . '.list', ['panel' => $this->decorator()]);
    }


    public function initDecorator()
    {
        $this->decorator = new PanelListDecorator($this);
    }


    public function initColumns()
    {
        $this->columns->put('name', [
            'label' => trans('panel.labels.name'),
            'order' => true,
            'action' => true,
            'handler' => function ($model, &$cell, &$row) {
                return $model->name;
            }
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


    public function urlTo($action, $params = null, array $apply = ['*'])
    {
        if (!str_contains($action, '@')) {
            $action = '\\' . static::class . '@' . $action;
        }

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


    public function policy()
    {
        if (is_null($this->policy)) {
            $this->initPolicy();
        }

        return $this->policy;
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
        if (!$this->columns->touched()) {
            $this->initColumns();
        }

        return $this->columns;
    }


    public function actions()
    {
        if (!$this->actions->touched()) {
            $this->initActions();
        }

        return $this->actions;
    }


    public function modelActions()
    {
        if (!$this->modelActions->touched()) {
            $this->initModelActions();
        }

        return $this->modelActions;
    }


    public function groupActions()
    {
        if (!$this->groupActions->touched()) {
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
        if (!$model = $this->findModel($primaryKey)) {
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
        if (empty($collection = $this->findModels($primaryKeys))) {
            throw new ModelNotFoundException;
        }

        return $collection;
    }


    public function allows($ability, $arguments = [])
    {
        $policy = $this->policy();

        if ($policy === null) {
            return false;
        }

        if (is_bool($policy)) {
            return $policy;
        }

        if (empty($arguments)) {
            $arguments = [$this->model()];
        }

        if (starts_with($ability, 'group-')) {
            $ability = preg_replace('/^group-/', '', $ability);

            if (empty($arguments)) {
                $arguments = [$this->model()];
            }

            foreach ($arguments as $model) {
                if ($this->denies($ability, $model)) {
                    return false;
                }
            }

            return true;
        }

        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        return is_callable([$policy, $ability]) && $policy->$ability(app('auth')->user(), ...$arguments);
    }


    public function denies($ability, $arguments = [])
    {
        return !$this->allows($ability, $arguments);
    }


    public function authorize($ability, $arguments = [])
    {
        if ($this->denies($ability, $arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }


    public function eachRow(&$row, $handlers = ['*'])
    {
        $this->apply($row, $handlers, $methodPrefix = 'eachRow');
    }


    public function config($key = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_get($this->config, $key, $default);
    }


    public function setConfig($key, $value)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_set($this->config, $key, $value);
    }


    protected function newModel()
    {
        $model = clone $this->model();

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
            if (!is_integer($key)) {
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

                if (!preg_match('/^apply' . preg_quote($methodPrefix) . '(.+)$/i', $method, $pock)) {
                    continue;
                }

                if (in_array($pock[1], $handlersExclude, true)) {
                    continue;
                }

                $this->{$method}($object);
            }
        } else {
            foreach ($handlers as $arg) {
                $method = 'apply' . $methodPrefix . $arg;

                if (is_callable([$this, $method])) {
                    $this->{$method}($object);
                }

            }
        }
    }

    public function initUrl()
    {
        $this->url = action('\\' . get_class($this) . '@showList');
    }

    static public function routes(array $attributes = null)
    {
        if (app()->routesAreCached()) {
            return;
        }

        $attributes = $attributes ?: ['middleware' => ['web']];

        if (empty($attributes['as'])) {
            $attributes['as'] = class_basename(static::class);
        }

        app('router')->group($attributes, function ($router) {
            static::initRoutes();
            static::initFeaturesRoutes();
        });
    }

    static protected function initRoutes()
    {
        app('router')->get('', '\\' . static::class . '@showList')->name('showList');
    }

    static protected function initFeaturesRoutes()
    {
        foreach (class_uses_recursive($class = get_called_class()) as $trait) {
            if (is_callable([$class, $method = 'routesFor' . class_basename($trait)])) {
                $class::$method();
            }
        }
    }
}