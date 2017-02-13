<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Collection;

class FieldsCollection extends Collection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolveItem($key, $field)
    {
        if ( ! is_null($key)) {
            $key = strval($key);
        }

        if ( ! is_array($field)) {
            $field = [ 'label' => strval($field ? $field : $key) ];
        }

        if (empty( $field['type'] )) {
            $field['type'] = 'text';
        }

        if (empty( $field['label'] )) {
            $field['label'] = $key;
        }

        if (isset( $field['before'] )) {
            $field['before'] = strval($field['before']);
        }

        if (isset( $field['after'] )) {
            $field['after'] = strval($field['after']);
        }

        if ( ! isset( $field['label'] )) {
            $field['label'] = $key;
        } else {
            $field['label'] = trans($field['label']);
        }

        return $field + [ 'key' => $key ];
    }


    public function offsetSet($key, $field)
    {
        if ($this->has($key)) {
            $this->pull($key);
        }

        if (isset( $field['before'] )) {
            return $this->before($key, array_except($field, 'before'), $field['before']);
        }

        if (isset( $field['after'] )) {
            return $this->after($key, array_except($field, 'after'), $field['after']);
        }

        parent::offsetSet($key, $field);
    }

}