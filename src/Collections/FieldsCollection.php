<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Toucheble;
use Illuminate\Database\Eloquent\Collection;

class FieldsCollection extends Collection
{
    use Toucheble, BeforeAndAfterCollection;

    protected $form;

    public function __construct($items = [])
    {
        if (func_num_args() > 0) {
            $this->touch();
        }

        parent::__construct($items);
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function offsetSet($key, $field)
    {
        if ($this->has($key)) {
            $this->pull($key);
        }

        if ([$field] && isset($field['before'])) {
            return array_before($this->items, $key, array_except($field, ['before', 'after']), $field['before']);
        } elseif (isset($field['after'])) {
            return array_after($this->items, $key, array_except($field, ['before', 'after']), $field['after']);
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
            $field = call_user_func($field, $this->form);
        }

        if (! is_array($field)) {
            $field = [];
        }

        if (empty($field['type'])) {
            $field['type'] = 'text';
        }

        if (empty($field['label'])) {
            $field['label'] = $key;
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
            $field['label'] = trans($field['label']);
        }

        return $field + ['key' => $key];
    }

    public function resolved()
    {
        return collect($this->items)->map(function ($field, $key) {
            return $this->resolve($key, $field);
        });
    }
}