<?php
namespace Dukhanin\Panel;

class PanelListDecorator
{

    protected $panel;

    protected $rows;

    protected $cache = [ ];


    public function __construct($panel)
    {
        $this->panel = $panel;
    }


    public function initRows()
    {
        $this->rows = collect();

        foreach ($this->items() as $model) {
            $row = [ 'model' => $model, 'cells' => [ ] ];

            foreach ($this->columns() as $columnKey => $column) {
                $cell            = &$row['cells'][$columnKey];
                $cell            = [ 'model' => $model, 'column' => $column, ];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset( $cell );

            $this->panel->eachRow($row);

            $this->rows->push($row, $model->getKey());
        }

        unset( $row );
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


    public function columns()
    {
        if ( ! isset( $this->cache['columns'] )) {
            $this->cache['columns'] = $this->panel->columns();
        }

        return $this->cache['columns'];
    }


    public function actions()
    {
        if ( ! isset( $this->cache['actions'] )) {
            $this->cache['actions'] = $this->panel->actions();
        }

        return $this->cache['actions'];
    }


    public function modelActions()
    {
        if ( ! isset( $this->cache['modelActions'] )) {
            $this->cache['modelActions'] = $this->panel->modelActions();
        }

        return $this->cache['modelActions'];
    }


    public function groupActions()
    {
        if ( ! isset( $this->cache['groupActions'] )) {
            $this->cache['groupActions'] = $this->panel->groupActions();
        }

        return $this->cache['groupActions'];
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
        $model  = $cell['model'];

        if (isset( $column['handler'] )) {
            $content = is_callable($column['handler']) ? $column['handler']($model, $cell, $row) : 'invalid handler';
        } else {
            $content = $model->getAttribute($column['key']);
        }

        $content = $this->linkCell($cell, $row, $content);

        return $content;
    }


    public function renderColumnHead($column, ...$overwrites)
    {
        $tag   = $column;
        $query = [ ];

        if (is_callable([ $this->panel, 'order' ]) && ! empty( $column['order'] )) {
            $thisColumnOrdered = $this->order() == $column['key'];
            $orderedDesc       = $this->orderDesc();
            $resetAnyOrder     = $thisColumnOrdered && $orderedDesc;

            if ( ! $resetAnyOrder) {
                $query += [ 'order' => $column['key'] ];

                if ($thisColumnOrdered && ! $orderedDesc) {
                    $query += [ 'orderDesc' => 1 ];
                }
            }

            if ($thisColumnOrdered) {
                $tag['icon'] = $orderedDesc ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
            }
        }

        return html_tag($tag, [
            'url'   => $this->url([ '!order', '!orderDesc', '!page' ] + $query),
            'title' => false,
            'width' => false
        ], ...$overwrites);
    }


    public function linkCell(&$cell, &$row, $content, $defaultAction = 'edit')
    {
        if ($action = value(array_get($cell, 'column.action'))) {
            $action = $action === true ? $defaultAction : $action;

            $url = $this->urlTo($action, $row['model']);
        } else {
            $url = value(array_get($cell, 'column.url'));
        }

        if (empty( $url )) {
            return $content;
        }

        return html_tag('a', [
            'content' => $content,
            'href'    => $url
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