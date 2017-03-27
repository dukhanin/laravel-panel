<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\ExtendedCollection;

class ColumnsCollection extends ExtendedCollection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolve($column, $key)
    {
        $valid = [
            'key'     => strval($key),
            'label'   => strval($key),
            'order'   => false,
            'handler' => null,
            'width'   => null
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

        $valid['label'] = trans($valid['label']);

        if(is_array($column)) {
            $valid = array_merge($valid, array_except($column, [ 'label', 'order', 'handler', 'key' ]));
        }

        return $valid;
    }
}