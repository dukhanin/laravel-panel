<?php

namespace App\Http\Controllers;

use App\Panel\Sample\ProductPanel;
use App\Panel\Sample\SectionPanel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class PanelSampleController extends Controller
{

    public function products(Request $request)
    {
        $panel = new ProductPanel;
        $panel->setUrl( action('PanelSampleController@products') );

        view()->inject('content', $panel->execute());

        return view('panel.sample-layout');
    }

    public function sections(Request $request)
    {
        $panel = new SectionPanel;
        $panel->setUrl( action('PanelSampleController@sections') );

        view()->inject('content', $panel->execute());

        return view('panel.sample-layout');
    }
}