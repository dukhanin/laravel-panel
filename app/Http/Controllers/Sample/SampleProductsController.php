<?php

namespace App\Http\Controllers\Sample;

use App\Sample\Product;
use App\Sample\ProductForm;
use App\Sample\Section;
use Dukhanin\Panel\Features\Categories;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\MoveTo;
use Dukhanin\Panel\Features\Order;
use Dukhanin\Panel\Features\Pages;
use Dukhanin\Panel\Features\Sort;
use Dukhanin\Panel\Controllers\PanelListController;

class SampleProductsController extends PanelListController
{

    use Sort, Order, Pages, EnableAndDisable, CreateAndEdit, Delete, Categories, MoveTo;


    public function before()
    {
        view()->share('header', 'Panel Sample');

        if ($inspinia = request()->query('inspinia')) {
            $this->configSet('views', 'panel-inspinia');
            $this->configSet('layout', 'panel-inspinia.layout');
        } else {
            $this->configSet('views', 'panel-bootstrap');
            $this->configSet('layout', 'panel-bootstrap.layout');
        }
    }


    public function initUrl()
    {
        $this->url = action('Sample\SampleProductsController@showList');

        if (request()->query('inspinia')) {
            $this->url = urlbuilder($this->url)->query([ 'inspinia' => 1 ])->compile();
        }
    }



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


    public function isSortEnabled()
    {
        return ! empty( $this->category ) && Section::byParent($this->category)->count() == 0;
    }


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


    public function newModel()
    {
        $model = parent::newModel();

        if ($this->category) {
            $model->section_id = $this->category;
        }

        return $model;
    }

}