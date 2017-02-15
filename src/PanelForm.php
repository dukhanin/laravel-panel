<?php

namespace Dukhanin\Panel;

use Dukhanin\Panel\Collections\ButtonsCollection;
use Dukhanin\Panel\Collections\FieldsCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use ErrorException;

class PanelForm
{

    protected $config;

    protected $model;

    protected $label;

    protected $uploadDirectory;

    protected $inputName;

    protected $data;

    protected $fields;

    protected $buttons;

    protected $view;

    protected $layout;

    protected $validator;

    protected $url;

    protected $eventDispatcher;


    public function __construct()
    {
        $this->buttons = new ButtonsCollection;
        $this->buttons->setPanel($this);

        $this->fields = new FieldsCollection;
        $this->fields->setPanel($this);
    }


    public function init()
    {
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


    public function initData()
    {
        if ($this->isSubmit()) {
            $this->initDataFromRequest();
        } elseif ( ! empty( $this->model )) {
            $this->initDataFromModel();
        } else {
            $this->initDataDefault();
        }
    }


    public function initDataFromRequest()
    {
        $this->data = $this->dataFromRequest();
    }


    public function initDataFromModel()
    {
        $this->data = $this->dataFromModel();
    }


    public function initDataDefault()
    {
        $this->data = [ ];
    }


    public function initValidator()
    {
        $this->validator = Validator::make([ ], [ ]);
    }


    public function initUrl()
    {
        $this->url = request()->fullUrl();
    }


    public function initButtons()
    {
        $this->buttons->put('submit');
    }


    public function initView()
    {
        $this->view = view($this->config('views') . '.form', [ 'form' => $this ]);
    }


    public function initLayout()
    {
        $this->layout = $this->config('layout');
    }


    public function initInputName()
    {
        $this->inputName = 'model';
    }


    public function initUploadDirectory()
    {
        $this->uploadDirectory = 'public/' . date('Y-m') . '/' . date('d');

        if ( ! Storage::exists($this->uploadDirectory)) {
            Storage::makeDirectory($this->uploadDirectory);
        }
    }


    public function initEventDispatcher()
    {
        $this->eventDispatcher = new Dispatcher();
    }


    public function onSuccess()
    {
        $this->eventDispatcher()->fire('success', $this);

        $this->fillModel();

        $this->saveModel();

        $this->eventDispatcher()->fire('succeed', $this);
    }


    public function onSubmit()
    {
        $this->eventDispatcher()->fire('submit', $this);

        $this->eventDispatcher()->fire('submited', $this);
    }


    public function onFailure()
    {
        $this->eventDispatcher()->fire('failure', $this);

        $this->eventDispatcher()->fire('failed', $this);
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


    public function validator()
    {
        if (empty( $this->validator )) {
            $this->initValidator();
        }

        return $this->validator;
    }


    public function url()
    {
        if (is_null($this->url)) {
            $this->initUrl();
        }

        return $this->url;
    }


    public function eventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->initEventDispatcher();
        }

        return $this->eventDispatcher;
    }


    public function submitUrl()
    {
        return $this->url();
    }


    public function method()
    {
        return 'POST';
    }


    public function fields()
    {
        if ( ! $this->fields->touched()) {
            $this->initFields();
        }

        return $this->fields;
    }


    public function fieldView($field)
    {
        $options = [
            "{$this->view()->getName()}.{$field['type']}",
            "{$this->config('views')}.form-fields.{$field['type']}",
            "{$this->config('views')}.form-fields.text"
        ];

        foreach ($options as $viewFile) {
            if ( ! view()->exists($viewFile)) {
                continue;
            }

            return $viewFile;
        }
    }


    public function inputName($name = null)
    {
        if (is_null($this->inputName)) {
            $this->initInputName();
        }

        if (is_null($name)) {
            return $this->inputName;
        }

        $inputName = [ ];
        if ($this->inputName) {
            $inputName[] = trim($this->inputName, '.');
        }

        $inputName[] = trim($name, '.');
        $inputName   = implode('.', $inputName);

        return $inputName;
    }


    public function htmlInputName($name = null)
    {
        $inputName = $this->inputName($name);
        $inputName = explode('.', $inputName);

        $first = array_shift($inputName);

        if (empty( $name )) {
            return $first;
        }

        $htmlName = $first;
        foreach ($inputName as $part) {
            $htmlName .= '[' . $part . ']';
        }

        return $htmlName;
    }


    public function fieldErrors($name)
    {
        if ( ! $this->isSubmit()) {
            return [ ];
        }

        return $this->validator()->errors()->get($name);
    }


    public function inputValue($name)
    {
        if (is_null($name)) {
            return null;
        }

        if ($this->fields->has("{$name}.value")) {
            return $this->fields->get("{$name}.value");
        }

        return $this->data($name);
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
        if ( ! $this->buttons->touched()) {
            $this->initButtons();
        }

        return $this->buttons;

    }


    public function data($name = null, $default = null)
    {
        if (is_null($this->data)) {
            $this->initData();
        }

        return array_get($this->data, $name, $default);
    }


    public function dataFromRequest($name = null, $default = null)
    {
        return request()->input($this->inputName($name), [ ]);
    }


    public function dataFromModel($name = null, $default = null)
    {
        if ($name !== null) {
            return $this->model()->getAttrubute($name, $default);
        }

        return $this->model()->attributesToArray();
    }


    public function dataDefault()
    {
        return [ ];
    }


    public function uploadDirectory()
    {
        if (is_null($this->uploadDirectory)) {
            $this->initUploadDirectory();
        }

        return $this->uploadDirectory;
    }


    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }


    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }


    public function setUrl($url)
    {
        $this->url = $url;
    }


    public function isFailure()
    {
        $validator = $this->validator();
        $validator->setData((array) $this->data());

        return $this->isSubmit() && $validator->fails();
    }


    public function isSuccess()
    {
        $validator = $this->validator();
        $validator->setData((array) $this->data());

        return $this->isSubmit() && $validator->passes();
    }


    public function isSubmit()
    {
        return request()->input($this->inputName());
    }


    public function submit($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('submit', $callback, $priority);
    }


    public function submited($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('submited', $callback, $priority);
    }


    public function success($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('success', $callback, $priority);
    }


    public function succeed($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('succeed', $callback, $priority);
    }


    public function failure($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('failure    ', $callback, $priority);
    }


    public function failed($callback, $priority = 0)
    {
        $this->eventDispatcher()->listen('failed    ', $callback, $priority);
    }


    public function __call($method, $arguments)
    {
        if (preg_match('/^(add)(.*?)?$/i', $method, $pock)) {
            $key = array_get($arguments, 0);

            if ( ! is_array($field = array_get($arguments, 1))) {
                $field = [ 'label' => $field ];
            };

            return $this->fields->put($key, [ 'type' => strtolower($pock[2]) ] + $field);
        }

        throw new ErrorException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }


    public function fillModel()
    {
        $this->model->fill($this->data());
    }


    public function saveModel()
    {
        $this->model->save();
    }


    public function clearData()
    {
        $this->data = null;
    }


    public function handle()
    {
        if ($this->isSubmit()) {
            $this->onSubmit();

            if ($this->isSuccess()) {
                $this->onSuccess();
            }

            if ($this->isFailure()) {
                $this->onFailure();
            }
        }
    }


    public function render()
    {
        return $this->view()->render();
    }


    public function execute()
    {
        $this->init();

        $this->handle();

        return $this->view();
    }


    public function config($key = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_get($this->config, $key, $default);
    }


    public function setConfig($key, $value)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_set($this->config, $key, $value);
    }
}