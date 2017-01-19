<?php

namespace App\Panel\Sample;

use Dukhanin\Panel\PanelForm;

class ProductForm extends PanelForm
{

    public function initLabel()
    {
        $this->label = 'Dukhanin\Panel\PanelForm';
    }


    public function initFields()
    {
        $this->addSelect('section_id', [
            'label'   => 'Section',
            'options' => Section::options()
        ]);
        $this->addText('name', 'Name');
        $this->addTextarea('description', 'Description');

        /* $this->addFile('image', 'Name');
        $this->addFiles('gallery', 'Name');
        $this->addText('settings.one', 'Some extra setting one');
        $this->addSelect('settings.two', [
            'label'   => 'Some extra setting two',
            'options' => Section::options()
        ]);
        $this->addCheckbox('settings.three', 'Some extra setting three');
        $this->addCheckbox('important', 'Important');*/

        $this->addCheckbox('enabled', 'Enabled');
    }
}