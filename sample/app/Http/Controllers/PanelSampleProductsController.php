<?php

namespace App\Http\Controllers;

use App\Panel\Sample\Product;
use App\Panel\Sample\ProductForm;
use App\Panel\Sample\Section;
use Dukhanin\Panel\Features\Categories;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\MoveTo;
use Dukhanin\Panel\Features\Order;
use Dukhanin\Panel\Features\Pages;
use Dukhanin\Panel\Features\Sort;
use Dukhanin\Panel\PanelList;

class PanelSampleProductsController extends PanelList
{

    use Sort, Order, Pages, EnableAndDisable, CreateAndEdit, Delete, Categories, MoveTo;


    public function before()
    {
        view()->share('header', 'Panel Sample');

        if ($inspinia = request()->query('inspinia')) {
            $this->setUrl(urlbuilder($this->url())->query([ 'inspinia' => 1 ])->compile());

            $this->configSet('views', 'panel-inspinia');
            $this->configSet('layout', 'panel-inspinia.layout');
        } else {
            $this->configSet('views', 'panel-bootstrap');
            $this->configSet('layout', 'panel-bootstrap.layout');
        }
    }


    public function initUrl()
    {
        $this->url = action('PanelSampleProductsController@showList');
    }


    public function initColumns()
    {
        $this->columns->put('name', [
            'name'  => 'Название',
            'order' => 'name'
        ]);
    }


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
        $this->categories = Section::options()->prepend('(All Sections)', '');
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

    public function initMoveToOptions()
    {
        $this->moveToOptions = Section::options();
    }


    public function moveTo($model, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $model->section_id = $section->getKey();
        $model->save();
    }

}