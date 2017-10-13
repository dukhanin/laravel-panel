<?php

namespace Dukhanin\Panel;

use Dukhanin\Panel\Collections\ButtonsCollection;
use Dukhanin\Panel\Collections\FieldsCollection;
use ErrorException;
use Dukhanin\Panel\Traits\HasAssets;
use Dukhanin\Panel\Traits\HasConfig;

class PanelShow
{
    use HasConfig, HasAssets;

    protected $model;

    protected $label;

    protected $fields;

    protected $buttons;

    protected $view;

    public function __construct()
    {
        $this->buttons = new ButtonsCollection;
        $this->buttons->setConfig($this->config());

        $this->fields = new FieldsCollection;
        $this->fields->setConfig($this->config());
    }

    public function initConfig()
    {
        $this->config = config('panel');
    }

    public function initModel()
    {
    }

    public function initLabel()
    {
        $this->label = '';
    }

    public function initFields()
    {
    }

    public function initButtons()
    {
        $this->buttons->put('back');
    }

    public function initView()
    {
        $this->view = view($this->config('views').'.show', ['show' => $this]);
    }

    public function model()
    {
        if (is_null($this->model)) {
            $this->initModel();
        }

        return $this->model;
    }

    public function label()
    {
        if (is_null($this->label)) {
            $this->initLabel();
        }

        return $this->label;
    }

    public function fields()
    {
        if (! $this->fields->touched()) {
            $this->initFields();
        }

        return $this->fields;
    }

    public function addField($key, $field)
    {
        $this->fields->put($key, $field);

        return $this;
    }

    public function fieldView($field)
    {
        $options = [
            array_get($field, 'view'),
            "{$this->view()->getName()}.{$field['type']}",
            "{$this->config('views')}.show-fields.{$field['type']}",
            "{$this->config('views')}.show-fields.text",
        ];

        foreach ($options as $viewFile) {
            if (! view()->exists($viewFile)) {
                continue;
            }

            return $viewFile;
        }

        throw new ErrorException('No view found for field '.array_get($field, 'key').'(searched in '.implode(', ', array_filter($options)).')');
    }

    public function view()
    {
        if (is_null($this->view)) {
            $this->initView();
        }

        return $this->view;
    }

    public function buttons()
    {
        if (! $this->buttons->touched()) {
            $this->initButtons();
        }

        return $this->buttons;
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function __call($method, $arguments)
    {
        if (starts_with(strtolower($method), 'add')) {
            return call_user_func_array([$this->fields, $method], $arguments);
        }

        throw new ErrorException('Call to undefined method '.get_class($this).'::'.$method.'()');
    }

    public function config($key = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_get($this->config, $key, $default);
    }

    public function value($name)
    {
        if (is_null($name)) {
            return null;
        }

        if ($value = array_get($this->fields, $name.'.value')) {
            return $value;
        }

        if ($handler = array_get($this->fields, $name.'.handler')) {
            return $handler($this->model(), $this);
        };

        return $this->model()->getAttribute($name);
    }
}