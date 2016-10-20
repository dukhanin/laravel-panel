<?php
namespace Dukhanin\Panel;

class PanelTreeDecorator extends PanelListDecorator
{

    public function initRows()
    {
        $this->rows = [ ];

        $this->initRowsRecursive();
    }


    protected function initRowsRecursive($parentKeyValue = null, $depth = 0)
    {
        if (is_null($parentKeyValue)) {
            $parentKeyValue = $this->panel->getParentKeyValue();
        }

        foreach ($this->getQueryBranch($parentKeyValue)->get() as $model) {
            $row = &$this->rows[$model->getKey()];
            $row = [ 'model' => $model, 'cells' => [ ], 'class' => 'depth-' . $depth ];

            foreach ($this->getColumns() as $columnKey => $column) {
                $cell            = &$row['cells'][$columnKey];
                $cell            = [ 'model' => $model, 'column' => $column, ];
                $cell['content'] = $this->renderCell($cell, $row);
            }
            unset( $cell );

            $this->panel->eachRow($row);

            $this->initRowsRecursive($model->getKey(), $depth + 1);
        }

        unset( $row );
    }
}