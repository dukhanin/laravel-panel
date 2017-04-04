<?php
namespace Dukhanin\Panel;

class PanelTreeDecorator extends PanelListDecorator
{

    public function initRows()
    {
        $this->rows = collect();

        $this->initRowsRecursive();
    }


    protected function initRowsRecursive($parentKeyValue = null, $depth = 0)
    {
        if (is_null($parentKeyValue)) {
            $parentKeyValue = $this->panel->parentKeyValue();
        }

        foreach ($this->queryBranch($parentKeyValue)->get() as $model) {
            $row = [ 'model' => $model, 'cells' => [], 'class' => 'depth-' . $depth ];

            $depthedColumnsKeys = $this->depthedColumnsKeys();

            foreach ($this->columns()->resolved() as $columnKey => $column) {
                $cell = &$row['cells'][$columnKey];
                $cell = [ 'model' => $model, 'column' => $column, ];

                if (in_array($columnKey, $depthedColumnsKeys)) {
                    html_tag_add_class($cell, 'panel-list-depth-cell');
                }

                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset($cell);

            $this->panel->eachRow($row);

            $this->rows->push($row, $model->getKey());

            $this->initRowsRecursive($model->getKey(), $depth + 1);
        }

        unset($row);
    }


    protected function depthedColumnsKeys()
    {
        $columns = $this->columns()->resolved();

        if ($keys = array_keys($columns->where('depth', true)->all())) {
            return $keys;
        }

        if ($keys = array_keys($columns->whereIn('key', [ 'name', 'title' ])->all())) {
            return $keys;
        }

        if ($keys = array_first(array_keys($columns->whereNot('depth', false)->all()))) {
            return $keys;
        }

        return [];
    }

}