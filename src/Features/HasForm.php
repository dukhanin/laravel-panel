<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelForm;

trait HasForm
{
    protected $form;

    public function initForm()
    {
        $this->form = new PanelForm;
    }

    public function setupForm()
    {
        $this->form->setConfig(null, $this->config());

        $this->form->buttons()->put('cancel', ['url' => $this->url()]);
        $this->form->buttons()->put('submit');

        if ($this->allows('edit', $this->model())) {
            $this->form->buttons()->put('apply');
        }
    }

    public function form()
    {
        if (is_null($this->form)) {
            $this->initForm();
            $this->setupForm();
        }

        return $this->form;
    }

    public function afterSave()
    {
        return function () {
            if (request()->input('_apply')) {
                return redirect()->to($this->urlTo('edit', ['id' => $this->form()->model()]));
            }

            return redirect()->to($this->url());
        };
    }
}