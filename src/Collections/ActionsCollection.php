<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Touchable;
use Illuminate\Support\Collection;

class ActionsCollection extends Collection
{
    use Touchable, BeforeAndAfterCollection;

    protected $panel;

    public function __construct($items = [])
    {
        if (func_num_args() > 0) {
            $this->touch();
        }

        parent::__construct($items);
    }

    public function setPanel($panel)
    {
        $this->panel = $panel;
    }

    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $value);

        $this->touch();
    }

    public function offsetUnset($key)
    {
        parent::offsetUnset($key);

        $this->touch();
    }

    public function offsetGet($key)
    {
        return $this->resolve($key, parent::offsetGet($key));
    }

    public function resolve($key, $action, $model = null)
    {
        if (is_string($action)) {
            $action = ['label' => $action];
        } elseif (is_callable($action)) {
            $action = call_user_func($action, $this->panel, $model);
        }

        if($action === false) {
            return false;
        }

        if (! is_array($action)) {
            $action = [];
        }

        $stack = [
            $this->panel->config("actions.{$key}", []),
            $this->panel->config('actions.default', []),
            [
                'key' => strval($key),
                'label' => strval($key),
                'global' => false,
                'class' => '',
                'icon' => null,
                'icon-only' => false,
            ],
        ];

        foreach ($stack as $arr) {
            $action = $action + (array) (is_callable($arr) ? call_user_func($arr, $this->panel, $model) : $arr);
        }

        $action['label'] = app('translator')->trans($action['label']);

        if (! empty($action['confirm'])) {
            $action['confirm'] = app('translator')->trans(strval($action['confirm']));
        }

        if (! isset($action['url'])) {
            $action['url'] = isset($action['action']) ? $this->panel->urlTo($action['action'], [$model]) : '#'.$key;
        }

        return $action;
    }

    public function resolved()
    {
        return collect($this->items)->map(function (&$action, $key) {
            return $this->resolve($key, $action);
        })->filter();
    }

    public function resolvedForModel($model)
    {
        return collect($this->items)->map(function (&$action, $key) use ($model) {
            return $this->resolve($key, $action, $model);
        })->filter();
    }

    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }
}