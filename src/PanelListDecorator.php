<?php
namespace Dukhanin\Panel;

use Dukhanin\Panel\Files\File;

class PanelListDecorator
{
    protected $panel;

    protected $rows;

    protected $cache = [];

    public function __construct($panel)
    {
        $this->panel = $panel;
    }

    public function initRows()
    {
        $this->rows = collect();

        foreach ($this->items() as $model) {
            $row = ['model' => $model, 'cells' => []];

            foreach ($this->columns()->resolved() as $columnKey => $column) {
                $cell = &$row['cells'][$columnKey];
                $cell = ['model' => $model, 'column' => $column,];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset($cell);

            $this->panel->eachRow($row);

            $this->rows->push($row, $model->getKey());
        }

        unset($row);
    }

    public function rows()
    {
        if (is_null($this->rows)) {
            $this->initRows();
        }

        return $this->rows;
    }

    public function categories()
    {
        return method_exists($this->panel, 'categories') ? $this->panel->categories() : collect();
    }

    public function moveToOptions()
    {
        return method_exists($this->panel, 'moveToOptions') ? $this->panel->moveToOptions() : collect();
    }

    public function paginator()
    {
        return method_exists($this->panel, 'paginator') ? $this->panel->paginator() : null;
    }

    public function perPageOptions()
    {
        if (method_exists($this->panel, 'perPageOptions')) {

            $options = $this->panel->perPageOptions();
            $options = array_map('intval', $options);
            $options = array_filter($options);

            if ($options && ($this->panel->total() > min($options))) {

                return $this->panel->perPageOptions();
            }
        }

        return null;
    }

    public function filter()
    {
        return method_exists($this->panel, 'filter') ? $this->panel->filter() : false;
    }

    public function isSortEnabled()
    {
        return method_exists($this->panel, 'isSortEnabled') ? $this->panel->isSortEnabled() : false;
    }

    public function isEmpty()
    {
        return $this->rows()->isEmpty();
    }

    public function renderAction($action, ...$overwrites)
    {
        if ($this->denies($action['key'])) {
            return '';
        }

        html_tag_add_class($action, $action['key']);

        return html_tag($action, ...$overwrites);
    }

    public function renderModelAction($action, $model = null, ...$overwrites)
    {
        if ($this->denies($action['key'], $model)) {
            return '';
        }

        $action = html_tag_add_class($action, $action['key']);

        return html_tag($action, ...$overwrites);
    }

    public function renderGroupAction($action, ...$overwrites)
    {
        if ($this->denies($action['key'])) {
            return '';
        }

        $action = html_tag_add_class($action, $action['key']);

        return html_tag($action, ...$overwrites);
    }

    public function renderCell(&$cell, &$row = null)
    {
        $column = $cell['column'];
        $model = $cell['model'];

        if (isset($column['handler'])) {
            $content = is_callable($column['handler']) ? $column['handler']($model, $cell, $row) : 'invalid handler';
        } else {
            $content = $this->cellContent($model->getAttribute($column['key']), $cell);
        }

        $content = $this->linkCell($cell, $row, $content);

        return $content;
    }

    public function renderColumnHead($column, ...$overwrites)
    {
        $tag = $column;
        $query = [];
        $url = null;

        if (method_exists($this->panel, 'order') && ! empty($column['order'])) {
            $thisColumnOrdered = $this->order() == $column['key'];
            $orderedDesc = $this->orderDesc();
            $resetAnyOrder = $thisColumnOrdered && $orderedDesc;

            if (! $resetAnyOrder) {
                $query += ['order' => $column['key']];

                if ($thisColumnOrdered && ! $orderedDesc) {
                    $query += ['orderDesc' => 1];
                }
            }

            if ($thisColumnOrdered) {
                $tag['icon'] = $orderedDesc ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
            }

            $url = $this->url(['!order', '!orderDesc', '!page'] + $query);
        }

        return html_tag($tag, [
            'url' => $url,
            'title' => false,
            'width' => false,
        ], ...$overwrites);
    }

    protected function cellContent($value, &$cell)
    {
        if ($value instanceof File) {
            return $this->asFile($value, $cell);
        }

        if (is_string($value)) {
            return e($value);
        }

        return $value;
    }

    protected function asFile($value, &$cell)
    {
        if (! $value->isDefined()) {
            return '';
        }

        if ($value->isImage()) {
            return $value->getResize(['panel_default', 'size' => '150xx150'])->img();
        }

        return html_tag('a', [
            'href' => $value->url(),
            'content' => $value->url(),
            'target' => '_blank',
        ]);
    }

    protected function linkCell(&$cell, &$row, $content, $defaultAction = 'edit')
    {
        if ($action = value(array_get($cell, 'column.action'))) {
            $action = $action === true ? $defaultAction : $action;

            $url = method_exists($this->panel, $action) ? $this->urlTo($action, $row['model']) : null;
        } else {
            $url = value(array_get($cell, 'column.url'));
        }

        if (empty($url)) {
            return $content;
        }

        return html_tag('a', [
            'content' => $content,
            'href' => $url,
        ]);
    }

    public function __call($method, $arguments)
    {
        return $this->panel->$method(...$arguments);
    }

    public function __get($name)
    {
        return $this->panel->$name;
    }
}