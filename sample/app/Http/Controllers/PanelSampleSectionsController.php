<?php

namespace App\Http\Controllers;

use App\Panel\Sample\Product;
use App\Panel\Sample\ProductForm;
use App\Panel\Sample\Section;
use App\Panel\Sample\SectionForm;
use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\Features\Order;
use Dukhanin\Panel\PanelTree;

class PanelSampleSectionsController extends PanelTree
{

    use Order, EnableAndDisable, CreateAndEdit, Delete;


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
        $this->url = action('PanelSampleSectionsController@showList');
    }


    /*
     * Base Panel settings
     */

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
}