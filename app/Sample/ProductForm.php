<?php

namespace App\Sample;

use Dukhanin\Panel\PanelForm;
use Illuminate\Support\Facades\Validator;

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
            'options' => Section::tree()->options('name')
        ]);

        $this->addText('name', 'Name');

        $this->addFiles('images', [
            'label'   => 'Image',
            'directory' => 'products',
            'resizes' => [
                [
                    'label' => 'Preview 100x100',
                    'size'  => '100x100'
                ],
                [
                    'label' => 'Square 50x50',
                    'size'  => '50xx50'
                ]
            ]
        ]);

        $this->addWysiwyg('description', 'Description');

        $this->addCheckbox('enabled', 'Enabled');
    }


    public function initValidator()
    {
        $this->validator = Validator::make([], [
            'name' => 'required'
        ]);
    }
}