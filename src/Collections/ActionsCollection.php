<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Collection;

class ActionsCollection extends Collection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolveItem($key, $action)
    {
        return $action;
    }


    public function resolve($key, $action, $model = null)
    {
        if (is_callable($action)) {
            $action = call_user_func($action, $this->panel, $model);
        }

        if ( ! is_array($action)) {
            $action = [ ];
        }

        $action = array_merge([
            'key'       => strval($key),
            'label'     => strval($key),
            'global'    => false,
            'class'     => '',
            'icon'      => null,
            'icon-only' => false
        ], $action);

        $action['label'] = trans($action['label']);

        if ( ! empty( $action['confirm'] )) {
            $action['confirm'] = trans($action['confirm']);
        }

        if ( ! isset( $action['url'] )) {
            $action['url'] = urlbuilder($this->panel->url())->append([
                camel_case($key),
                $model ? $model->id : ''
            ])->compile();
        }

        return $action;
    }


    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }


    public function resolved($model = null)
    {
        $actions = collect();

        $this->each(function ($action, $key) use ($actions, $model) {
            $actions->put($key, $this->resolve($key, $action, $model));
        });

        return $actions;
    }


    public function getIterator()
    {
        return $this->resolved()->getIterator();
    }
}