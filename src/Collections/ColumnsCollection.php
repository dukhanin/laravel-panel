<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\ResolvedCollection;

class ColumnsCollection extends ResolvedCollection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolveItemOnGet($key, $column)
    {
        $valid = [
            'key'              => strval($key),
            'label'            => strval($key),
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
}