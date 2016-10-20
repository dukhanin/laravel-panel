<?php

namespace App\Panel\Sample;

use Dukhanin\Panel\Features\CreateAndEdit;
use Dukhanin\Panel\Features\Delete;
use Dukhanin\Panel\Features\EnableAndDisable;
use Dukhanin\Panel\PanelTree;

class SectionPanel extends PanelTree
{

    use EnableAndDisable, CreateAndEdit, Delete;


    /*
     * Base Panel settings
     */
    public function initModel()
    {
        $this->model = new Section();
    }


    public function initPolicy()
    {
        $this->policy = true;
    }


    public function initForm()
    {
        $this->form = new SectionForm;

        $this->form->addCancelButton([ 'url' => $this->getUrl() ]);
        $this->form->addSubmitButton([ 'url' => $this->getUrl() ]);
        $this->form->addApplyButton();
    }
}