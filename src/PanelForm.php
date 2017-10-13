<?php

namespace Dukhanin\Panel;

use Dukhanin\Panel\Collections\ButtonsCollection;
use Dukhanin\Panel\Collections\FieldsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use ErrorException;
use Closure;
use Dukhanin\Panel\Traits\HasAssets;
use Dukhanin\Panel\Traits\HasConfig;

class PanelForm
{
    use HasConfig, HasAssets;

    protected $model;

    protected $label;

    protected $uploadDirectory;

    protected $inputName;

    protected $mergeAttributes = [];

    protected $data;

    protected $fields;

    protected $buttons;

    protected $view;

    protected $validator;

    protected $eventDispatcher;

    protected $errors;

    protected $method = 'post';

    protected $submitUrl = '';

    public function __construct()
    {
        $this->buttons = new ButtonsCollection;
        $this->buttons->setConfig($this->config());

        $this->fields = new FieldsCollection;
        $this->fields->setConfig($this->config());

        $this->method = 'post';

        $this->submitUrl = '';
    }

    public function initModel()
    {
    }

    public function initLabel()
    {
        $this->label = $this->config($this->model() && $this->model()->exists ? 'labels.edit' : 'labels.create');
    }

    public function initUploadDirectory()
    {
        $this->uploadDirectory = date('Y-m').'/'.date('d');

        if (! Storage::exists($this->uploadDirectory)) {
            Storage::makeDirectory($this->uploadDirectory);
        }
    }

    public function initFields()
    {
    }

    public function initData()
    {
        if ($this->isSubmit()) {
            $this->initDataFromInput();
        } elseif (array_has(request()->old(), $this->inputName())) {
            $this->initDataFromOld();
        } elseif (! empty($this->model)) {
            $this->initDataFromModel();
        } else {
            $this->initDataDefault();
        }
    }

    protected function initDataFromInput()
    {
        $this->data = (array) request()->input($this->inputName());

        $this->mergeAttributes($this->data);
    }

    protected function initDataFromOld()
    {
        $this->data = (array) request()->old($this->inputName());

        $this->mergeAttributes($this->data);
    }

    protected function mergeAttributes(array &$data)
    {
        if (empty($this->model())) {
            return;
        }

        $dataFromModel = $this->dataFromModel();

        foreach ($this->mergeAttributes as $key) {
            if (! array_has($data, $key) || ! array_has($dataFromModel, $key)) {
                continue;
            }

            $data[$key] = (array) $data[$key] + (array) $dataFromModel[$key];
        }
    }

    protected function initDataFromModel()
    {
        $this->data = (array) $this->dataFromModel();
    }

    protected function initDataDefault()
    {
        $this->data = [];
    }

    protected function initValidator()
    {
        $this->validator = Validator::make([], []);
    }

    public function initButtons()
    {
        $this->buttons->put('submit', [
            'url' => $this->submitUrl(),
        ]);
    }

    public function initView()
    {
        $this->view = view($this->config('views').'.form', ['form' => $this]);
    }

    public function initInputName()
    {
        $this->inputName = 'model';
    }

    public function initEventDispatcher()
    {
        $this->eventDispatcher = new Dispatcher();
    }

    public function onSuccess()
    {
        $this->eventDispatcher()->fire('success', $this);

        if ($this->model()) {
            $this->fillModel();

            $this->saveModel();
        }

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

        throw new ValidationException($this->validator());
    }

    public function model()
    {
        if (is_null($this->model)) {
            $this->initModel();
        }

        return $this->model;
    }

    public function modelAttributes()
    {
        return $this->model()->attributesToArray();
    }

    public function modelRelations()
    {
        if (! $this->fields->touched()) {
            $this->initFields();
        }

        $relations = [];

        foreach ($this->fields->resolved()->where('relation', true) as $field) {
            if (! is_callable([$this->model(), $field['key']])) {
                continue;
            }

            if (! ($relation = $this->model()->{$field['key']}()) instanceof Relation) {
                continue;
            }

            $relations[$field['key']] = $relation;
        }

        return $relations;
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
        if (empty($this->validator)) {
            $this->initValidator();
        }

        return $this->validator;
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
        return $this->submitUrl;
    }

    public function setSubmitUrl(string $url)
    {
        return $this->submitUrl = $url;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function method()
    {
        return $this->method;
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
            "{$this->config('views')}.form-fields.{$field['type']}",
            "{$this->config('views')}.form-fields.text",
        ];

        foreach ($options as $viewFile) {
            if (! view()->exists($viewFile)) {
                continue;
            }

            return $viewFile;
        }

        throw new ErrorException('No view found for field '.array_get($field, 'key').'(searched in '.implode(', ', array_filter($options)).')');
    }

    public function inputName($name = null)
    {
        if (is_null($this->inputName)) {
            $this->initInputName();
        }

        if (is_null($name)) {
            return $this->inputName;
        }

        $inputName = [];
        if ($this->inputName) {
            $inputName[] = trim($this->inputName, '.');
        }

        $inputName[] = trim($name, '.');
        $inputName = implode('.', $inputName);

        return $inputName;
    }

    public function htmlInputName($name = null)
    {
        $inputName = $this->inputName($name);
        $inputName = explode('.', $inputName);

        $first = array_shift($inputName);

        if (empty($name)) {
            return $first;
        }

        $htmlName = $first;
        foreach ($inputName as $part) {
            $htmlName .= '['.$part.']';
        }

        return $htmlName;
    }

    public function initErrors()
    {
        $this->errors = request()->session()->get('errors') ?: new ViewErrorBag;
    }

    public function errors()
    {
        if (is_null($this->errors)) {
            $this->initErrors();
        }

        return $this->errors;
    }

    public function fieldErrors($name)
    {
        return $this->errors()->get($name);
    }

    public function inputValue($name)
    {
        if (is_null($name)) {
            return null;
        }

        if ($value = array_get($this->fields, $name.'.value')) {
            return $value instanceof Closure ? $value($this->model()) : $value;
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
        if (! $this->buttons->touched()) {
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

    public function setData($name, $value)
    {
        if (is_null($this->data)) {
            $this->initData();
        }

        return array_set($this->data, $name, $value);
    }

    public function dataFromRequest()
    {
        return request()->input($this->inputName());
    }

    public function dataFromModel()
    {
        return $this->modelAttributes() + array_map(function ($relation) {
                $results = $relation->getResults();

                if ($results instanceof Collection) {
                    return $results->modelKeys();
                }

                if (! empty($results)) {
                    return $results->getKey();
                }
            }, $this->modelRelations());
    }

    public function dataDefault()
    {
        return [];
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

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function isFailure()
    {
        return $this->isSubmit() && ! $this->isSuccess();
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
        if (starts_with(strtolower($method), 'add')) {
            if (! $this->fields->touched() && ! collect(debug_backtrace(null, 4))->contains('function', 'initFields')) {
                $this->initFields();
            }

            return call_user_func_array([$this->fields, $method], $arguments);
        }

        throw new ErrorException('Call to undefined method '.get_class($this).'::'.$method.'()');
    }

    public function fillModel()
    {
        $this->model->fill($this->data());
    }

    public function saveModel()
    {
        $this->saveModelAttributes();

        $this->saveModelRelations();
    }

    public function saveModelAttributes()
    {
        $this->model->save();
    }

    public function saveModelRelations()
    {
        foreach ($this->modelRelations() as $relationKey => $relation) {
            if ($relation instanceof BelongsTo) {
                $relation->dissociate();

                if ($keys = intval($this->data($relationKey))) {
                    $relation->associate($keys);
                }

                continue;
            }

            if (in_array(InteractsWithPivotTable::class, class_uses_recursive($relation))) {
                $relationMethod = array_get($this->fields->resolved()->get($relationKey), 'relationMethod', 'sync');

                $relation->{$relationMethod}((array) $this->data($relationKey));
                continue;
            }
        }

        empty($this->model->getDirty()) || $this->model->save();
    }

    public function clearData()
    {
        $this->data = null;
    }

    public function handle($successResponse = null)
    {
        $response = null;

        if ($this->isSubmit()) {
            $this->onSubmit();
        }

        if ($this->isSuccess()) {
            $response = $this->onSuccess();

            if (! is_null($successResponse)) {
                $response = $successResponse instanceof Closure ? $successResponse($this) : $successResponse;
            }
        } elseif ($this->isFailure()) {
            $response = $this->onFailure();
        }

        return ! is_null($response) ? $response : $this->view();
    }
}