<?php

namespace App\Http\Controllers;

use App\Panel\Sample\ProductPanel;
use App\Panel\Sample\SectionPanel;
use Illuminate\Http\Request;

class PanelSampleController extends Controller
{

    protected $panel;


    protected function initTheme()
    {
        $request = Request::capture();

        if ($request->query('inspinia')) {
            $this->panel->configSet('views', 'panel-inspinia');
            $this->panel->configSet('layout', 'panel-inspinia.layout');

            $this->panel->setUrl(urlbuilder($this->panel->getUrl())->query([ 'inspinia' => 1 ])->compile());
        } else {
            $this->panel->configSet('views', 'panel-bootstrap');
            $this->panel->configSet('layout', 'panel-bootstrap.layout');
        }

    }


    public function products()
    {
        view()->share('header', 'Panel Sample');

        $this->panel = new ProductPanel;
        $this->panel->setUrl(action('PanelSampleController@products'));

        $this->initTheme();

        return $this->panel->execute();
    }


    public function sections()
    {
        view()->share('header', 'Panel Sample');

        $this->panel = new SectionPanel;
        $this->panel->setUrl(action('PanelSampleController@sections'));

        $this->initTheme();

        return $this->panel->execute();
    }
}