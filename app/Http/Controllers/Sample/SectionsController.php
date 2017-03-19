<?php

namespace App\Http\Controllers\Sample;

use App\Sample\Section;
use App\Sample\SectionForm;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\Order;
use Dukhanin\Panel\Controllers\PanelTreeController;
use Dukhanin\Panel\Files\File;

class SectionsController extends PanelTreeController
{

    use Order, EnableAndDisable, CreateAndEdit, Delete;


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
            'handler' => function ($section) {
                if ($file = File::find($section->image)) {
                    return $file->getResize('50x50')->img();
                }
            }
        ]);

        $this->columns->put('name', [
            'label' => 'Section Name',
            'action' => 'edit',
            'depth' => true,
            'order' => true
        ]);
    }


    public function initUrl()
    {
        $this->url = action('Sample\SectionsController@showList');

        if (request()->query('inspinia')) {
            $this->url = urlbuilder($this->url)->query([ 'inspinia' => 1 ])->compile();
        }
    }


    public function initLabel()
    {
        $this->label = 'Dukhanin\Panel\PanelTree';
    }


    public function initModel()
    {
        $this->model = new Section;
    }


    public function initPolicy()
    {
        $this->policy = true;
    }


    public function initForm()
    {
        $this->form = new SectionForm;
    }


    public function newModel()
    {
        $model = parent::newModel();

        return $model;
    }


    protected function applyUrlTheme($url)
    {
        if (request()->query('inspinia')) {
            $url->query([ 'inspinia' => 1 ]);
        }
    }
}