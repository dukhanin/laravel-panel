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

        foreach ($this->getList() as $model) {
            $row = &$this->rows[$model->getKey()];
            $row = [ 'model' => $model, 'cells' => [ ], 'class' => [ ] ];

            foreach ($this->getColumns() as $columnKey => $column) {
                $cell            = &$row['cells'][$columnKey];
                $cell            = [ 'model' => $model, 'column' => $column, ];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset( $cell );

            $this->panel->eachRow($row);
        }

        unset( $row );
    }


    public function getRows()
    {
        if (is_null($this->rows)) {
            $this->initRows();
        }

        return $this->rows;
    }


    public function getCategories()
    {
        return method_exists($this->panel, 'getCategories') ? $this->panel->getCategories() : collect();
    }


    public function getMoveTo()
    {
        return method_exists($this->panel, 'getMoveTo') ? $this->panel->getMoveTo() : collect();
    }


    public function getPaginator()
    {
        return method_exists($this->panel, 'getPaginator') ? $this->panel->getPaginator() : null;
    }


    public function getColumns()
    {
        if ( ! isset( $this->cache['columns'] )) {
            $this->cache['columns'] = $this->panel->getColumns();
        }

        return $this->cache['columns'];
    }


    public function getActions()
    {
        if ( ! isset( $this->cache['actions'] )) {
            $this->cache['actions'] = $this->panel->getActions();
        }

        return $this->cache['actions'];
    }


    public function getGroupActions()
    {
        if ( ! isset( $this->cache['groupActions'] )) {
            $this->cache['groupActions'] = $this->panel->getGroupActions();
        }

        return $this->cache['groupActions'];
    }


    public function isSortEnabled()
    {
        return method_exists($this->panel, 'isSortEnabled') ? $this->panel->isSortEnabled() : false;
    }


    public function isEmpty()
    {
        return empty( $this->getRows() );
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
        if (empty( $column['order'] )) {
            return $column['label'];
        }

        $thisColumnOrdered = $this->panel->getOrder() == $column['key'];
        $orderedDesc       = $this->panel->getOrderDesc();
        $resetAnyOrder     = $thisColumnOrdered && $orderedDesc;

        $url = urlbuilder($this->panel->getUrl([ '!order', '!orderDesc', '!pages' ]));
        $tag = array_only($column, [ 'label', 'width', 'order', 'class', 'attributes' ]);

        if ( ! $resetAnyOrder) {
            $url->query([ $this->getRequestAttributeName('order') => $column['key'] ]);

            if ($thisColumnOrdered && ! $orderedDesc) {
                $url->query([ $this->getRequestAttributeName('orderDesc') => 1 ]);
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
            $url    = $this->allows($action, $model) ? urlbuilder($this->getUrl())->append([
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