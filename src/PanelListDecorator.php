<?php
namespace Dukhanin\Panel;

class PanelListDecorator
{

    protected $panel;

    protected $rows;

    protected $cache = [ ];


    public function __construct(PanelList $list)
    {
        $this->panel = $list;
    }


    public function initRows()
    {
        $this->rows = [ ];

        foreach ($this->items() as $model) {
            $row = &$this->rows[$model->getKey()];
            $row = [ 'model' => $model, 'cells' => [ ], 'class' => [ ] ];

            foreach ($this->columns() as $columnKey => $column) {
                $cell            = &$row['cells'][$columnKey];
                $cell            = [ 'model' => $model, 'column' => $column, ];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset( $cell );

            $this->eachRow($row);
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
        return empty( $this->rows() );
    }


    public function renderAction($action, ...$overwrites)
    {
        if (is_callable($action)) {
            $action = $action($this->panel);
        }

        html_tag_add_class($action, $action['key']);

        return html_tag($action, ...$overwrites);
    }


    public function renderModelAction($action, $model = null, ...$overwrites)
    {
        if (is_callable($action)) {
            $action = $action($model, $this->panel);
        }

        $action = html_tag_add_class($action, $action['key']);

        return html_tag($action, ...$overwrites);
    }


    public function renderGroupAction($action, ...$overwrites)
    {
        if (is_callable($action)) {
            $action = $action($this->panel);
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
        if ( ! is_callable([ $this->panel, 'order' ]) || empty( $column['order'] )) {
            return $column['label'];
        }

        $thisColumnOrdered = $this->order() == $column['key'];
        $orderedDesc       = $this->orderDesc();
        $resetAnyOrder     = $thisColumnOrdered && $orderedDesc;

        $url = urlbuilder($this->url([ '!order', '!orderDesc', '!pages' ]));
        $tag = array_only($column, [ 'label', 'width', 'order', 'class', 'attributes' ]);

        if ( ! $resetAnyOrder) {
            $url->query([ 'order' => $column['key'] ]);

            if ($thisColumnOrdered && ! $orderedDesc) {
                $url->query([ 'orderDesc' => 1 ]);
            }
        }

        if ($thisColumnOrdered) {
            $tag['icon'] = $orderedDesc ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
        }

        return html_tag($tag, [ 'url' => $url->compile(), 'title' => false ], ...$overwrites);
    }


    public function linkCell(&$cell, &$row, $content, $defaultAction = 'edit')
    {
        $model = $cell['model'];

        if ($action = value(array_get($cell, 'column.action'))) {
            $action = $action === true ? $defaultAction : $action;
            $url    = $this->allows($action, $model) ? urlbuilder($this->url())->append([
                $action,
                $model->id
            ])->compile() : null;
        } else {
            $url = value(array_get($cell, 'column.url'));
        }

        if (empty( $url )) {
            return $content;
        }

        return html_tag('a', [
            'content'         => $content,
            'attributes.href' => $url
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