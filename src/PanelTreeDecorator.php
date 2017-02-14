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
            $row = [ 'model' => $model, 'cells' => [ ], 'class' => 'depth-' . $depth ];

            foreach ($this->columns() as $columnKey => $column) {
                $cell            = &$row['cells'][$columnKey];
                $cell            = [ 'model' => $model, 'column' => $column, ];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset( $cell );

            $this->panel->eachRow($row);

            $this->rows->push($row, $model->getKey());

            $this->initRowsRecursive($model->getKey(), $depth + 1);
        }

        unset( $row );
    }
}