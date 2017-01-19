<?php

namespace App\Panel\Sample;

use Dukhanin\Panel\Features\Categories;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\MoveTo;
use Dukhanin\Panel\Features\Pages;
use Dukhanin\Panel\Features\Sort;
use Dukhanin\Panel\PanelList;

class ProductPanel extends PanelList
{

    use Sort, Pages, EnableAndDisable, CreateAndEdit, Delete, Categories, MoveTo;


    /*
     * Base Panel settings
     */

    public function initLabel()
    {
        $this->label = 'Dukhanin\Panel\PanelList';
    }


    public function initModel()
    {
        $this->model = new Product;
    }


    public function initPolicy()
    {
        $this->policy = true;
    }


    public function initForm()
    {
        $this->form = new ProductForm;

        $this->form->addCancelButton([ 'url' => $this->getUrl() ]);
        $this->form->addSubmitButton([ 'url' => $this->getUrl() ]);
        $this->form->addApplyButton();
    }


    protected function applyQueryDefaultOrder($select)
    {
        if ($this->isSortEnabled()) {
            $select->ordered();
        } else {
            $select->orderBy('id', 'asc');
        }
    }


    /**
     * Sort
     */

    public function isSortEnabled()
    {
        return ! empty( $this->category ) && Section::byParent($this->category)->count() == 0;
    }


    /**
     * Categories
     */

    public function initCategories()
    {
        $this->categories = Section::options()->prepend('(All Sections)');
    }


    public function applyQueryCategory($query)
    {
        if ( ! empty( $this->category )) {
            $query->bySectionRecursive($this->category);
        }
    }


    /**
     * MoveTo
     */

    public function initMoveTo()
    {
        $this->moveTo = Section::options();
    }


    public function moveTo($model, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $model->section_id = $section->getKey();
        $model->save();
    }

}