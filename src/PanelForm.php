<?php

namespace Dukhanin\Panel;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Dukhanin\Support\Traits\DispatchesEvents;
use UnexpectedValueException;
use ErrorException;

class PanelForm
{

    use DispatchesEvents;

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


    public function __construct($viewFile = null)
    {
        if ($viewFile !== null) {
            $this->viewFile = $viewFile;
        }
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
        $this->data = $this->getDataFromRequest();
    }


    public function initDataFromModel()
    {
        $this->data = $this->getDataFromModel();
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
        $this->url = Request::fullUrl();
    }


    public function initButtons()
    {
        $this->addSubmitButton();
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


    public function onSuccess()
    {
        $this->fireEvent('success');

        $this->fillModel();
        $this->saveModel();

        $this->fireEvent('succeed');
    }


    public function onSubmit()
    {
        $this->fireEvent('submit');
        $this->fireEvent('submited');
    }


    public function onFailure()
    {
        $this->fireEvent('failure');
        $this->fireEvent('failed');
    }


    public function getModel()
    {
        if (is_null($this->model)) {
            $this->initModel();
        }

        return $this->model;
    }


    public function getLabel()
    {
        if (is_null($this->label)) {
            $this->initLabel();
        }

        return $this->label;
    }


    public function getValidator()
    {
        if (empty( $this->validator )) {
            $this->initValidator();
        }

        return $this->validator;
    }


    public function getUrl()
    {
        if (is_null($this->url)) {
            $this->initUrl();
        }

        return $this->url;
    }


    public function getSubmitUrl()
    {
        return $this->getUrl();
    }


    public function getMethod()
    {
        return 'POST';
    }


    public function getFields()
    {
        if (is_null($this->fields)) {
            $this->initFields();
        }

        return (array) $this->fields;
    }


    public function getFieldView($field)
    {
        $options = [
            $this->getView()->getName() . '.' . $field['type'],
            $this->config('views') . '.form-fields.' . $field['type'],
            $this->config('views') . '.form-fields.text'
        ];

        foreach ($options as $viewFile) {
            if ( ! view()->exists($viewFile)) {
                continue;
            }

            return $viewFile;
        }
    }


    public function getInputName($name = null)
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


    public function getHtmlInputName($name = null)
    {
        $inputName = $this->getInputName($name);
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


    public function getFieldErrors($name)
    {
        if ( ! $this->isSubmit()) {
            return [ ];
        }

        return $this->getValidator()->errors()->get($name);
    }


    public function getInputValue($name)
    {
        if (is_null($name)) {
            return null;
        }

        if (array_has($this->fields, $name . '.value')) {
            return $this->fields[$name]['value'];
        }

        return $this->getData($name);
    }


    public function getView()
    {
        if (is_null($this->view)) {
            $this->initView();
        }

        return $this->view;
    }


    public function getLayout()
    {
        if (is_null($this->layout)) {
            $this->initLayout();
        }

        return $this->layout;
    }


    public function getButtons()
    {
        if (is_null($this->buttons)) {
            $this->initButtons();
        }

        $buttons = [ ];

        foreach ($this->buttons as $key => $button) {
            $button                  = $this->validateButton($key, $button);
            $buttons[$button['key']] = $button;
        }

        return $buttons;

    }


    public function getData($name = null, $default = null)
    {
        if (is_null($this->data)) {
            $this->initData();
        }

        return array_get($this->data, $name, $default);
    }


    public function getDataFromRequest($name = null, $default = null)
    {
        return Request::input($this->getInputName($name), [ ]);
    }


    public function getDataFromModel($name = null, $default = null)
    {
        if ($name !== null) {
            return $this->getAttrubute($name, $default);
        }

        return $this->model->attributesToArray();
    }


    public function getDataDefault()
    {
        return [ ];
    }


    public function getUploadDirectory()
    {
        if (is_null($this->uploadDirectory)) {
            $this->initUploadDirectory();
        }

        return $this->uploadDirectory;
    }


    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
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


    public function addField($key = null, $settings = [ 'type' => 'text' ])
    {
        $field = $this->validateField($key, $settings);

        if ( ! empty( $field['key'] ) && isset( $this->fields[$field['key']] )) {
            unset( $this->fields[$field['key']] );
        }

        if (isset( $settings['before'] )) {
            array_before($this->fields, $field['key'], $field, $field['before']);
        } elseif (isset( $settings['after'] )) {
            array_after($this->fields, $field['key'], $field, $field['after']);
        } elseif ( ! is_null($field['key'])) {
            $this->fields[$field['key']] = $field;
        } else {
            $this->fields[] = $field;
        }

        return $this;
    }


    public function removeField($key)
    {
        $key = strval($key);

        if (isset( $this->fields[$key] )) {
            unset( $this->fields[$key] );
        }

        return $this;
    }


    public function addButton($key, $button = [ ])
    {
        $key = strval($key);

        if (is_array($button) || is_callable($button)) {
            $this->buttons[$key] = $button;
        } else {
            throw new UnexpectedValueException('Invalid type for button value. Button must be array or callable');
        }

        return $this;
    }


    public function addSubmitButton(array $button = [ ])
    {
        $this->addButton('submit', array_merge($this->config('buttons.submit'), $button));

        if ( ! empty( $this->buttons['submit']['url'] )) {
            $this->succeed(function ($form) {
                abort(301, '', [ 'Location' => $form->buttons['submit']['url'] ]);
            }, -2);
        }
    }


    public function addCancelButton(array $button = [ ])
    {
        $this->addButton('cancel', array_merge($this->config('buttons.cancel'), $button));
    }


    public function addApplyButton(array $button = [ ])
    {
        $this->addButton('apply', array_merge($this->config('buttons.apply'), $button));

        $this->succeed(function ($form) {
            if ( ! Request::input('_apply')) {
                return;
            }

            if ($this->model->wasRecentlyCreated) {
                $url = urlbuilder(Request::fullUrl())->pop('/create')->append([
                    'edit',
                    $this->model->getKey()
                ])->compile();
            } else {
                $url = Request::server('HTTP_REFERER');
            }

            abort(301, '', [ 'Location' => $url ]);
        }, -1);
    }


    public function addButtonBefore($keyBefore, $key, $button = [ ])
    {
        $keyBefore      = strval($keyBefore);
        $keys           = array_keys($this->buttons);
        $keyBeforeIndex = array_search($keyBefore, $keys);

        $this->addButton($key, $button);

        if ($keyBeforeIndex !== false) {
            $button = $this->buttons[$key];
            unset( $this->buttons[$key] );

            $left  = array_slice($this->buttons, 0, $keyBeforeIndex);
            $right = array_slice($this->buttons, $keyBeforeIndex);

            $left[$key]    = $button;
            $this->buttons = $left + $right;
        }

        return $this;
    }


    public function addButtonAfter($keyAfter, $key = null, $button = [ ])
    {
        $keyAfter      = strval($keyAfter);
        $keys          = array_keys($this->buttons);
        $keyAfterIndex = array_search($keyAfter, $keys);

        if ($keyAfterIndex === false || ( $keyAfterIndex + 1 ) == count($keys)) {
            return $this->addButton($key, $button);
        }

        return $this->addButtonBefore($keys[$keyAfterIndex + 1], $key, $button);
    }


    public function removeButton($button)
    {
        if (is_array($button) && isset( $button['key'] )) {
            $key = $button['key'];
        } else {
            $key = strval($button);
        }

        if (isset( $this->buttons[$key] )) {
            unset( $this->buttons[$key] );
        }

        return $this;
    }


    public function isFailure()
    {
        $validator = $this->getValidator();
        $validator->setData((array) $this->getData());

        return $this->isSubmit() && $validator->fails();
    }


    public function isSuccess()
    {
        $validator = $this->getValidator();
        $validator->setData((array) $this->getData());

        return $this->isSubmit() && $validator->passes();
    }


    public function isSubmit()
    {
        return Request::input($this->getInputName());
    }


    public function submit($callback, $priority = 0)
    {
        $this->registerEvent('submit', $callback, $priority);
    }


    public function submited($callback, $priority = 0)
    {
        $this->registerEvent('submited', $callback, $priority);
    }


    public function success($callback, $priority = 0)
    {
        $this->registerEvent('success', $callback, $priority);
    }


    public function succeed($callback, $priority = 0)
    {
        $this->registerEvent('succeed', $callback, $priority);
    }


    public function failure($callback, $priority = 0)
    {
        $this->registerEvent('failure    ', $callback, $priority);
    }


    public function failed($callback, $priority = 0)
    {
        $this->registerEvent('failed    ', $callback, $priority);
    }


    public function __call($method, $arguments)
    {
        if (preg_match('/^(add)(.*?)?$/i', $method, $pock)) {
            $field         = $this->validateField(isset( $arguments[0] ) ? $arguments[0] : null,
                isset( $arguments[1] ) ? $arguments[1] : null);
            $field['type'] = strtolower($pock[2]);

            return $this->addField($field['key'], $field);
        }

        throw new ErrorException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }


    public function fillModel()
    {
        $this->model->fill($this->getData());
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
        return $this->getView()->render();
    }


    public function execute()
    {
        $this->init();
        $this->handle();

        return $this->getView();
    }


    protected function validateButton($key, $button)
    {
        if (is_callable($button)) {
            $button = call_user_func($button, $this);
        }

        $_button = array_merge($this->config('buttons.default'), [ 'key' => $key ], (array) $button);

        if (empty( $_button['label'] )) {
            $_button['label'] = $key;
        }

        if (isset( $button['url'] )) {
            $_button['url'] = $button['url'];
        } else {
            $_button['url'] = $this->getSubmitUrl();
        }

        if (isset( $button['type'] )) {
            $_button['type'] = $button['type'];
        } elseif ( ! isset( $_button['type'] )) {
            $_button['type'] = $key === 'submit' ? 'submit' : 'button';
        }

        $_button['label'] = trans($_button['label']);

        return $_button;
    }


    protected function validateField($key = null, $settings = null)
    {
        if ( ! is_null($key)) {
            $key = strval($key);
        }

        if ( ! is_array($settings)) {
            $settings = [ 'label' => strval($settings ? $settings : $key) ];
        }

        if (empty( $settings['type'] )) {
            $settings['type'] = 'text';
        }

        if (empty( $settings['label'] )) {
            $settings['label'] = $key;
        }

        if (isset( $settings['before'] )) {
            $settings['before'] = strval($settings['before']);
        }

        if (isset( $settings['after'] )) {
            $settings['after'] = strval($settings['after']);
        }

        if ( ! isset( $settings['label'] )) {
            $settings['label'] = $key;
        } else {
            $settings['label'] = trans($settings['label']);
        }

        return $settings + [ 'key' => $key ];
    }


    public function config($key = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_get($this->config, $key, $default);
    }


    public function configSet($key, $value)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_set($this->config, $key, $value);
    }
}