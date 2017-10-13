<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Toucheble;
use Illuminate\Support\Collection;

class ColumnsCollection extends Collection
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

    public function resolve($key, $column)
    {
        if (is_string($column)) {
            $column = ['label' => $column];
        } elseif (is_callable($column)) {
            $column = call_user_func($column, $this->panel);
        }

        if (! is_array($column)) {
            $column = [];
        }

        $column = $column + [
                'key' => strval($key),
                'label' => strval($key),
                'order' => false,
                'handler' => null,
                'width' => null,
            ];

        if (isset($column['order'])) {
            $column['order'] = $column['order'] === true ? $column['key'] : $column['order'];
        }

        $column['label'] = app('translator')->trans($column['label']);

        return $column;
    }

    public function resolved()
    {
        return collect($this->items)->map(function ($column, $key) {
            return $this->resolve($key, $column);
        });
    }
}