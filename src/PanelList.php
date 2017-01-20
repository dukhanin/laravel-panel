<?php

namespace Dukhanin\Panel;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Dukhanin\Support\Traits\HandlesActions;
use Mockery\CountValidator\Exception;

class PanelList
{

    use HandlesActions;

    protected $model;

    protected $label;

    protected $order;

    protected $orderDesc;

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


    public function actionIndex()
    {
        return $this->getView()->render();
    }


    public function init()
    {
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


    public function initOrder()
    {
        $this->order     = $this->getRequestAttribute('order');
        $this->orderDesc = $this->getRequestAttribute('orderDesc', false);
    }


    public function initQuery()
    {
        $this->query = $this->getModel()->newQuery();
    }


    public function initPolicy()
    {
        try {
            $this->policy = Gate::getPolicyFor($this->getModel());
        } catch (InvalidArgumentException $e) {

        }
    }


    public function initView()
    {
        $this->view = view($this->config('views') . '.list', [ 'decorator' => $this->getDecorator() ]);
    }


    public function initLayout()
    {
        $this->layout = $this->config('layout');
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


    public function getUrl(array $apply = [ '*' ])
    {
        if (empty( $this->url )) {
            $this->initUrl();
        }

        $url = urlbuilder($this->url);
        $this->apply($url, $apply, 'applyUrl');

        return $url->compile();
    }


    public function getModel()
    {
        if (is_null($this->model)) {
            $this->initModel();
        }

        return $this->model;
    }


    public function getLabel()
    {
        if (is_null($this->label)) {
            $this->initLabel();
        }

        return $this->label;
    }


    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->initOrder();
        }

        return $this->order;
    }


    public function getOrderDesc()
    {
        if (is_null($this->orderDesc)) {
            $this->initOrder();
        }

        return $this->orderDesc;
    }


    public function setLayout($layout)
    {
        $this->layout = $layout;
    }


    public function isOrderDesc()
    {
        return $this->getOrderDesc();
    }


    public function getQuery(array $apply = [ '*' ])
    {
        if (is_null($this->query)) {
            $this->initQuery();
        }

        if ( ! ( $this->query instanceof \Illuminate\Database\Query\Builder ) && ! ( $this->query instanceof \Illuminate\Database\Eloquent\Builder ) && ! ( $this->query instanceof \Illuminate\Database\Eloquent\Relations\Relation )) {
            throw new UnexpectedValueException(__CLASS__ . '::query attribute is empty or invalid');
        }

        $select = clone $this->query;
        $this->apply($select, $apply, 'applyQuery');

        return $select;
    }


    public function getList(array $apply = [ '*' ])
    {
        return $this->getQuery($apply)->get();
    }


    public function getPolicy()
    {
        if (is_null($this->policy)) {
            $this->initPolicy();
        }

        return $this->policy;
    }


    public function getView()
    {
        if (is_null($this->view)) {
            $this->initView();
        }

        return $this->view;
    }


    public function getLayout()
    {
        if (is_null($this->layout)) {
            $this->initLayout();
        }

        return $this->layout;
    }


    public function getDecorator()
    {
        if (is_null($this->decorator)) {
            $this->initDecorator();
        }

        return $this->decorator;
    }


    public function getColumns()
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


    public function getTotal(array $apply = [ ])
    {
        $apply[] = '!pages';

        return $this->getQuery($apply)->count();
    }


    public function getActions()
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


    public function getModelActions($model = null)
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


    public function getGroupActions()
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


    public function getRequestAttribute($attribute, $default = null)
    {
        $attribute = $this->getRequestAttributeName($attribute);

        return $this->getRequest()->query->get($attribute, $default);
    }


    public function getRequestAttributeName($attribute)
    {
        return strval($attribute);
    }


    public function findModel($primaryKey)
    {
        return $this->getQuery([ '!pages', '!order' ])->find($primaryKey);
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
        return $this->getQuery([ '!pages' ])->findMany($primaryKeys);
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
        $policy = $this->getPolicy();

        if (starts_with($ability, 'group-')) {
            $ability = preg_replace('/^group-/', '', $ability);

            if (empty( $arguments )) {
                $arguments = [ $this->getModel() ];
            }

            foreach ($arguments as $model) {
                if ($this->denies($ability, $model)) {
                    return false;
                }
            }

            return true;
        }

        if ($policy === null) {
            return false;
        }

        if (is_bool($policy)) {
            return $policy;
        }

        if ( ! is_array($arguments)) {
            $arguments = [ $arguments ];
        }

        if (empty( $arguments )) {
            $arguments = [ $this->getModel() ];
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
        $model = clone $this->getModel();

        return $model;
    }


    protected function validateColumn($columnKey, $column)
    {
        $_column = [
            'key'              => strval($columnKey),
            'label'            => strval($columnKey),
            'order'            => false,
            'handler'          => null,
            'attributes.width' => null
        ];

        if (is_string($column)) {
            $_column['label'] = $column;
        } elseif (isset( $column['label'] )) {
            $_column['label'] = strval($column['label']);
        }

        if (isset( $column['order'] )) {
            $_column['order'] = $column['order'] === true ? $_column['key'] : $column['order'];
        }

        if (isset( $column['handler'] )) {
            $_column['handler'] = $column['handler'];
        }

        if (isset( $column['width'] )) {
            $_column['attributes.width'] = $column['width'];
        }

        $_column['label'] = trans($_column['label']);

        $_column = array_merge($_column, array_except($column, [ 'label', 'order', 'handler', 'width', 'key' ]));

        return $_column;
    }


    protected function validateAction($actionKey, $action, $model = null)
    {
        $actionDefaults = [
            'key'       => strval($actionKey),
            'label'     => strval($actionKey),
            'global'    => false,
            'class'     => '',
            'url'       => urlbuilder($this->getUrl())->append([
                camel_case($actionKey),
                $model ? $model->id : ''
            ])->compile(),
            'icon'      => null,
            'icon-only' => false
        ];

        $action = $this->resolveAction($actionKey, $action, $model);

        $action = array_merge($actionDefaults, $action);

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


    protected function applyQueryDefaultOrder($select)
    {

    }


    protected function applyQueryOrder($select)
    {
        $columns = $this->getColumns();

        if (empty( $columns[$this->order]['order'] )) {
            return;
        }

        $select->getQuery()->orders = null;

        $order = $columns[$this->order]['order'];

        if (is_callable($order)) {
            call_user_func($order, $select, $this);
        } else {
            $select->orderBy($order, $this->orderDesc ? 'desc' : 'asc');
        }
    }


    protected function applyUrlOrder(&$url)
    {
        $query = [ ];

        if ($this->order) {
            $query[$this->getRequestAttributeName('order')] = $this->order;
        }

        if ($this->orderDesc) {
            $query[$this->getRequestAttributeName('orderDesc')] = 1;
        }

        $url = $url->query($query);
    }
}