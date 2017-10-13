<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelFilter;
use Illuminate\Support\Collection;

trait Filter
{
    protected $filter;

    public function initFilter()
    {
        $this->filter = new PanelFilter;
    }

    public function filter()
    {
        if (is_null($this->filter)) {
            $this->initFilter();
            $this->setupFilter();
        }

        return $this->filter;
    }

    public function setupFilter()
    {
        $this->filter()->setConfig(null, $this->config());

        if ($this->filter()->data()) {
            $this->filter()->buttons()->put('reset', ['url' => $this->url(['!filter'])]);
        }

        $this->filter()->buttons()->put('filter');
    }

    public function applyQueryFilter($query)
    {
        foreach ($this->filter()->fields()->resolved() as $field) {
            $apply = array_get($field, 'filter');

            if ($apply === false) {
                continue;
            }

            if (in_array($value = $this->filter()->data($field['key']), [null, ''], true)) {
                continue;
            }

            if (! is_callable($apply)) {
                $apply = [$this, 'filterDefault'];
            }

            $apply($query, $value, $this, $field);
        }
    }

    public function applyFormFilter($form)
    {
        foreach ($this->filter()->fields()->resolved() as $field) {
            $apply = array_get($field, 'model');

            if ($apply === false) {
                continue;
            }

            if (! is_callable($apply)) {
                $apply = [$this, 'setFormDataFromFilter'];
            }

            call_user_func($apply, $form, $field['key'], $this->filter()->data($field['key']), $this);
        }
    }

    public function applyUrlFilter(&$url)
    {
        if ($data = $this->filter()->data()) {
            $url->query([$this->filter()->inputName() => $data]);
        }
    }

    protected function filterDefault($query, $value, $filter, $field)
    {
        switch (array_get($field, 'as')) {
            case 'text':
                $this->filterAsText($query, $value, $filter, $field);
                break;

            case 'relation':
                $this->filterAsRelation($query, $value, $filter, $field);
                break;

            default:
                $this->filterAsScalar($query, $value, $filter, $field);
        }
    }

    protected function setFormDataFromFilter($form, $key, $value, $filter)
    {
        if ($form->isSubmit() || array_key_exists($key, $form->data())) {
            return;
        }

        $form->setData($key, $value);
    }

    protected function filterAsText($query, $value, $filter, $field)
    {
        if (empty($value = $this->resolveFilterValue($value, $field))) {
            return;
        }

        $boolean = array_get($field, 'boolean', 'and');

        $column = array_get($field, 'filter', array_get($field, 'key'));

        $operator = array_get($field, 'operator', 'like');

        $query->where(function ($query) use ($column, $operator, $value, $boolean) {
            foreach ($value as $substring) {
                $substring = strtolower($operator) == 'like' ? '%'.$substring.'%' : $substring;

                $query->where($column, $operator, $substring, $boolean);
            }
        });
    }

    protected function filterAsRelation($query, $value, $filter, $field)
    {
        if (empty($value = $this->resolveFilterValue($value, $field))) {
            return;
        }

        $relation = array_get($field, 'filter', array_get($field, 'key'));

        $query->whereHas($relation, function ($query) use ($value) {
            $query->whereKey($value);
        });
    }

    protected function filterAsScalar($query, $value, $filter, $field)
    {
        if (empty($value = $this->resolveFilterValue($value, $field))) {
            return;
        }

        $boolean = array_get($field, 'boolean', 'and');

        $column = array_get($field, 'filter', array_get($field, 'key'));

        $value = $this->resolveFilterValue($value, $field);

        $query->whereIn($column, $value, $boolean);
    }

    protected function resolveFilterValue($value, $field)
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        $value = (array) $value;

        $castAs = strtolower(array_get($field, 'as'));

        switch ($castAs) {
            case 'int':
            case 'integer':
                $value = array_map(function ($value) {
                    if ($value === '') {
                        return null;
                    }

                    return intval($value);
                }, $value);
                break;

            case 'text':
                $value = is_array($value) ? implode(' ', $value) : strval($value);
                $value = preg_replace('/[^[:alnum:][:space:]]+/u', ' ', $value);
                $value = explode(' ', $value);
                break;

            default:
                $value = array_map('strval', $value);
        }

        return $value;
    }
}