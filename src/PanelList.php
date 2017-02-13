<?php

namespace Dukhanin\Panel;

use Dukhanin\Support\Traits\HasUrl;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PanelList
{

    use HasUrl;

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


    public function callAction($action, $parameters)
    {
        $this->init();

        if (is_callable([ $this, 'before' ])) {
            $this->before();
        }

        $res = call_user_func_array([ $this, $action ], $parameters);

        if (is_callable([ $this, 'after' ])) {
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
        try {
            $this->policy = Gate::policyFor($this->model());
        } catch (InvalidArgumentException $e) {

        }
    }


    public function initView()
    {
        $this->view = view($this->config('views') . '.list', [ 'panel' => $this->decorator() ]);
    }


    public function initDecorator()
    {
        $this->decorator = new PanelListDecorator($this);
    }


    public function initColumns()
    {
        $this->columns = [
            'name' => [
                'label'   => trans('panel.labels.name'),
                'order'   => true,
                'action'  => true,
                'handler' => function ($model, &$cell, &$row) {
                    return $model->name;
                }
            ],
        ];
    }


    public function initActions()
    {
        $this->actions = [ ];
    }


    public function initModelActions()
    {
        $this->modelActions = [ ];
    }


    public function initGroupActions()
    {
        $this->groupActions = [ ];
    }


    public function url(array $apply = [ '*' ])
    {
        if (empty( $this->url )) {
            $this->initUrl();
        }

        $url = urlbuilder($this->url);
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


    public function query(array $apply = [ '*' ])
    {
        if (is_null($this->query)) {
            $this->initQuery();
        }

        $select = clone $this->query;

        $this->apply($select, $apply, 'applyQuery');

        return $select;
    }


    public function items(array $apply = [ '*' ])
    {
        return $this->query($apply)->get();
    }


    public function total(array $apply = [ ])
    {
        $apply[] = '!pages';

        $builder = $this->query($apply);

        $builder->getQuery()->orders      = [ ];
        $builder->getQuery()->unionOrders = [ ];

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
        if (is_null($this->columns)) {
            $this->initColumns();
        }

        $columns = [ ];

        foreach ($this->columns as $columnKey => $column) {
            $column                  = $this->validateColumn($columnKey, $column);
            $columns[$column['key']] = $column;
        }

        return $columns;
    }


    public function actions()
    {
        $actions = [ ];

        foreach ($this->actions as $actionKey => $action) {
            $action = $this->validateAction($actionKey, $action);

            if ($this->allows($action['key'])) {
                $actions[$action['key']] = $action;
            }
        }

        return $actions;
    }


    public function modelActions($model = null)
    {
        $actions = [ ];

        foreach ($this->modelActions as $actionKey => $action) {
            $action = $this->validateAction($actionKey, $action, $model);

            if ($this->allows($action['key'], $model)) {
                $actions[$action['key']] = $action;
            } else {
                $actions[$action['key']] = null;
            }
        }

        return $actions;

    }


    public function groupActions()
    {
        $actions = [ ];

        foreach ($this->groupActions as $actionKey => $action) {
            $action = $this->validateAction($actionKey, $action);

            if ($this->allows($action['key'])) {
                $actions[$action['key']] = $action;
            }
        }

        return $actions;

    }


    public function input($key = null, $default = null)
    {
        return request()->input($key, $default);
    }


    public function findModel($primaryKey)
    {
        return $this->query([ '!pages', '!order' ])->find($primaryKey);
    }


    public function findModelOrFail($primaryKey)
    {
        if ( ! $model = $this->findModel($primaryKey)) {
            throw new ModelNotFoundException;
        }

        return $model;
    }


    public function findModels($primaryKeys)
    {
        return $this->query([ '!pages' ])->findMany($primaryKeys);
    }


    public function findModelsOrFail($primaryKeys)
    {
        if (empty( $collection = $this->findModels($primaryKeys) )) {
            throw new ModelNotFoundException;
        }

        return $collection;
    }


    public function allows($ability, $arguments = [ ])
    {
        $policy = $this->policy();

        if ($policy === null) {
            return false;
        }

        if (is_bool($policy)) {
            return $policy;
        }

        if (empty( $arguments )) {
            $arguments = [ $this->model() ];
        }

        if (starts_with($ability, 'group-')) {
            $ability = preg_replace('/^group-/', '', $ability);

            if (empty( $arguments )) {
                $arguments = [ $this->model() ];
            }

            foreach ($arguments as $model) {
                if ($this->denies($ability, $model)) {
                    return false;
                }
            }

            return true;
        }

        if ( ! is_array($arguments)) {
            $arguments = [ $arguments ];
        }

        return method_exists($policy, $ability) && $policy->$ability(Auth::user(), ...$arguments);
    }


    public function denies($ability, $arguments = [ ])
    {
        return ! $this->allows($ability, $arguments);
    }


    public function authorize($ability, $arguments = [ ])
    {
        if ($this->denies($ability, $arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }


    public function eachRow(&$row, $handlers = [ '*' ])
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


    public function configSet($key, $value)
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


    protected function validateColumn($columnKey, $column)
    {
        $valid = [
            'key'              => strval($columnKey),
            'label'            => strval($columnKey),
            'order'            => false,
            'handler'          => null,
            'attributes.width' => null
        ];

        if (is_string($column)) {
            $valid['label'] = $column;
        } elseif (isset( $column['label'] )) {
            $valid['label'] = strval($column['label']);
        }

        if (isset( $column['order'] )) {
            $valid['order'] = $column['order'] === true ? $valid['key'] : $column['order'];
        }

        if (isset( $column['handler'] )) {
            $valid['handler'] = $column['handler'];
        }

        if (isset( $column['width'] )) {
            $valid['attributes.width'] = $column['width'];
        }

        $valid['label'] = trans($valid['label']);

        $valid = array_merge($valid, array_except($column, [ 'label', 'order', 'handler', 'width', 'key' ]));

        return $valid;
    }


    protected function validateAction($actionKey, $action, $model = null)
    {
        $defaults = [
            'key'       => strval($actionKey),
            'label'     => strval($actionKey),
            'global'    => false,
            'class'     => '',
            'url'       => urlbuilder($this->url())->append([
                camel_case($actionKey),
                $model ? $model->id : ''
            ])->compile(),
            'icon'      => null,
            'icon-only' => false
        ];

        $action = $this->resolveAction($actionKey, $action, $model);

        $action = array_merge($defaults, $action);

        $action['label'] = trans($action['label']);

        if ( ! empty( $action['confirm'] )) {
            $action['confirm'] = trans($action['confirm']);
        }

        return $action;
    }


    protected function resolveAction($actionKey, $action, $model = null)
    {
        if (is_callable($action)) {
            $action = call_user_func($action, $this, $model);
        }

        if ( ! is_array($action)) {
            $action = [ ];
        }

        return $action;
    }


    protected function apply(&$object, array $list, $methodPrefix = '')
    {
        if (in_array(false, $list, true)) {
            return;
        }

        $methodPrefix    = preg_replace('/^apply/i', '', $methodPrefix);
        $handlers        = array_map('strtolower', $list);
        $handlersExclude = [ ];

        foreach ($handlers as $name) {
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

                if ( ! preg_match('/^apply' . preg_quote($methodPrefix) . '(.+)$/i', $method, $pock)) {
                    continue;
                }

                if (in_array($pock[1], $handlersExclude, true)) {
                    continue;
                }

                $this->{$method}($object);
            }
        } else {
            foreach ($handlers as $arg) {
                $method = $methodPrefix . $arg;
                $this->{$method}($object);
            }
        }
    }


    static public function routes($name = null)
    {
        $className = '\\' . get_called_class();

        $route = app('router')->get('', "{$className}@showList");

        if ( ! is_null($name)) {
            $route->name($name);
        }

        foreach (class_uses(get_called_class()) as $trait) {
            $method = 'routesFeature' . class_basename($trait);

            if (is_callable([ $trait, $method ])) {
                $trait::$method($className);
            }
        }

    }
}