<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Toucheble;
use Illuminate\Support\Collection;

class ActionsCollection extends Collection
{
    use Toucheble, BeforeAndAfterCollection;

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

        if (! is_array($action)) {
            $action = [];
        }

        $action = $action + [
                'key' => strval($key),
                'label' => strval($key),
                'global' => false,
                'class' => '',
                'icon' => null,
                'icon-only' => false,
            ];

        $action['label'] = trans($action['label']);

        if (! empty($action['confirm'])) {
            $action['confirm'] = trans($action['confirm']);
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
        });
    }

    public function resolvedForModel($model)
    {
        return collect($this->items)->map(function (&$action, $key) use ($model) {
            return $this->resolve($key, $action, $model);
        });
    }

    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }
}