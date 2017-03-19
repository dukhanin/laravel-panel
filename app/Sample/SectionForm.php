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
        $this->addSelect('parent', [
            'label'     => 'Parent Section',
            'relation'  => true,
            'nullTitle' => false,
            'options'   => Section::where('id', '!=', $this->model->getKey())->tree()->options('name')->prepend('(Root)', 0)
        ]);

        $this->addText('name', 'Name');

        $this->addFile('image', [
            'label'     => 'Image',
            'directory' => 'sections'
        ]);

        $this->addWysiwyg('description', 'Description');

        $this->addCheckbox('enabled', 'Enabled');
    }
}