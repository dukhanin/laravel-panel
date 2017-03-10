<?php

namespace App\Sample;

use Dukhanin\Panel\PanelForm;

class SectionForm extends PanelForm
{

    public function initLabel()
    {
        $this->label = 'Dukhanin\Panel\PanelForm';
    }


    public function initFields()
    {
        $this->addSelect('parent_id', [
            'label'     => 'Parent Section',
            'nullTitle' => false,
            'options'   => Section::options([ 'except' => $this->model->getKey(), 'depth' => 1 ])->prepend('(Root)', 0)
        ]);

        $this->addText('name', 'Name');

        $this->addFile('image', [
            'label'     => 'Image',
            'directory' => 'sections'
        ]);

        $this->addTextarea('description', 'Description');

        $this->addCheckbox('enabled', 'Enabled');
    }
}