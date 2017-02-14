<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Controllers;
use Dukhanin\Panel\Traits\PanelTreeTrait;

abstract class PanelTreeController extends Controller
{

    use PanelTreeTrait;
}