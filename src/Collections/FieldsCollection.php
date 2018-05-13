<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Panel\Traits\HasConfig;
use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Touchable;
use Illuminate\Database\Eloquent\Collection;
use ErrorException;

class FieldsCollection extends Collection
{
    use Touchable, BeforeAndAfterCollection, HasConfig;

    protected $form;

    public function __construct($items = [])
    {
        if (func_num_args() > 0) {
            $this->touch();
        }

        parent::__construct($items);
    }

    public function offsetSet($key, $field)
    {
        if ($this->has($key)) {
            $this->pull($key);
        }

        if (isset($field['before'])) {
            return array_before($this->items, $key, array_except($field, [
                'before',
                'after',
            ]), is_string($field['before']) ? $field['before'] : null);
        } elseif (isset($field['after'])) {
            return array_after($this->items, $key, array_except($field, [
                'before',
                'after',
            ]), is_string($field['after']) ? $field['after'] : null);
        } else {
            parent::offsetSet($key, $field);
        }

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

    public function resolve($key, $field)
    {
        if (! is_null($key)) {
            $key = strval($key);
        }

        if (is_string($field)) {
            $field = ['label' => $field];
        } elseif (is_callable($field)) {
            $field = ['handler' => $field];
        }

        if (! is_array($field)) {
            $field = [];
        }

        if (! isset($field['label'])) {
            $field['label'] = $key;
        }

        if (empty($field['type'])) {
            $field['type'] = 'text';
        }

        if (isset($field['before'])) {
            $field['before'] = strval($field['before']);
        }

        if (isset($field['after'])) {
            $field['after'] = strval($field['after']);
        }

        if (! isset($field['label'])) {
            $field['label'] = $key;
        } else {
            $field['label'] = app('translator')->trans($field['label']);
        }

        return $field + ['key' => $key];
    }

    public function resolved()
    {
        return collect($this->items)->map(function ($field, $key) {
            return $this->resolve($key, $field);
        });
    }

    public function __call($method, $arguments)
    {
        if (preg_match('/^(add)(.*?)?$/i', $method, $pock)) {
            $key = array_get($arguments, 0);

            if (! is_array($field = array_get($arguments, 1))) {
                $field = ['label' => $field];
            };

            return $this->put($key, ['type' => kebab_case($pock[2])] + $field);
        }

        throw new ErrorException('Call to undefined method '.get_class($this).'::'.$method.'()');
    }
}