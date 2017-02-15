<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\ResolvedCollection;

class ActionsCollection extends ResolvedCollection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolveItemOnGet($key, $action)
    {
        if (is_callable($action)) {
            return $action;
        }

        return $this->validAction($key, $action);
    }


    public function resolveItemOnIteration($key, $action)
    {
        if (is_callable($action)) {
            $action = call_user_func($action, $this->panel);

            return $this->validAction($key, $action);
        }

        return $action;
    }


    public function resolvedForModel($model)
    {
        return $this->raw()->map(function (&$action, $key) use ($model) {
            if (is_callable($action)) {
                $action = call_user_func($action, $this->panel, $model);
            }

            return $this->validAction($key, $action, $model);
        });
    }


    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }


    protected function validAction($key, $action, $model = null)
    {
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
            $action['url'] = isset( $action['action'] ) ? $this->panel->urlTo($action['action'], $model) : '#' . $key;
        }

        return $action;
    }
}