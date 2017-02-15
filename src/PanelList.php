<?php

namespace Dukhanin\Panel;

use Dukhanin\Panel\Traits\PanelListTrait;

class PanelList
{
    use PanelListTrait;

    public function run()
    {
        $this->init();

        if (method_exists($this, 'before')) {
            $this->before();
        }

        if (method_exists($this, 'after')) {
            $this->after();
        }

        return $this->view();
    }
}