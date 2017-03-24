<?php
namespace Dukhanin\Panel;

use Exception;

class PanelFilter extends PanelForm
{
    public function method()
    {
        return 'get';
    }

    public function initInputName()
    {
        $this->inputName = 'f';
    }


    public function initView()
    {
        $this->view = view($this->config('views') . '.filter', ['form' => $this]);
    }

    public function fieldView($field)
    {
        $options = [
            array_get($field, 'view'),
            "{$this->view()->getName()}.{$field['type']}",
            "{$this->config('views')}.filter-fields.{$field['type']}",
            "{$this->config('views')}.filter-fields.text"
        ];

        foreach ($options as $viewFile) {
            if (!view()->exists($viewFile)) {
                continue;
            }

            return $viewFile;
        }

        throw new Exception('No view found for field ' . array_get($field, 'key')
            . '(searched in ' . implode(', ', array_filter($options)) . ')');
    }

    public function initButtons()
    {
        $this->buttons->put('filter');
    }
}