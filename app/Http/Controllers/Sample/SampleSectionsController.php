<?php

namespace App\Http\Controllers\Sample;

use App\Sample\Section;
use App\Sample\SectionForm;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\Order;
use Dukhanin\Panel\Controllers\PanelTreeController;

class SampleSectionsController extends PanelTreeController
{

    use Order, EnableAndDisable, CreateAndEdit, Delete;


    public function before()
    {
        view()->share('header', 'Panel Sample');
    }


    public function initConfig()
    {
        $this->config = config(request()->query('inspinia') ? 'panel-inspinia' : 'panel-bootstrap');
    }


    public function initUrl()
    {
        $this->url = action('Sample\SampleSectionsController@showList');

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
}