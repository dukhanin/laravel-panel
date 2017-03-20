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
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Controllers\PanelListController;

class ProductsController extends PanelListController
{

    use Sort, Order, Pages, EnableAndDisable, CreateAndEdit, Delete, Categories, MoveTo;


    public function before()
    {
        view()->share('header', 'Panel Sample');

        // config settings for sample only
        $this->setConfig('views', request()->query('inspinia') ? 'panel::panel-inspinia' : 'panel::panel-bootstrap');
        $this->setConfig('layout', request()->query('inspinia') ? 'panel::panel-inspinia.layout' : 'panel::panel-bootstrap.layout');
    }


    public function initColumns()
    {
        $this->columns->put('image', [
            'label'   => 'Image',
            'width'   => 100,
            'action'  => 'edit',
            'handler' => function ($product) {
                if ($file = File::find(array_first($product->images))) {
                    return $file->getResize('50x50')->img();
                }
            }
        ]);

        $this->columns->put('name', [
            'label'  => 'Product Name',
            'action' => 'edit',
            'order'  => true
        ]);
    }


    public function initUrl()
    {
        $this->url = action('Sample\ProductsController@showList');
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


    public function isSortEnabled()
    {
        return ! empty($this->category) && Section::byParent($this->category)->count() == 0;
    }


    public function initCategories()
    {
        $this->categories = Section::tree()->options('name')->prepend('(All Sections)', '');
    }


    public function initMoveToOptions()
    {
        $this->moveToOptions = Section::tree()->options('name');
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


    protected function applyUrlTheme($url)
    {
        if (request()->query('inspinia')) {
            $url->query([ 'inspinia' => 1 ]);
        }
    }


    protected function applyQueryDefaultOrder($select)
    {
        if ($this->isSortEnabled()) {
            $select->ordered();
        } else {
            $select->orderBy('id', 'asc');
        }
    }


    protected function applyQueryCategory($query)
    {
        if ( ! empty($this->category)) {
            $query->bySectionRecursive($this->category);
        }
    }
}