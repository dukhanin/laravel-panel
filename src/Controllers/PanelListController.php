<?php

namespace Dukhanin\Panel\Controllers;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Controllers;
use Dukhanin\Panel\Traits\PanelListTrait;

abstract class PanelListController extends Controller
{
    use PanelListTrait;
}