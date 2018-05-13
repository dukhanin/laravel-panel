<?php

namespace Dukhanin\Panel\Collections;

use Illuminate\Support\Collection;
use Dukhanin\Panel\Traits\HasConfig;
use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Touchable;

class ButtonsCollection extends Collection
{
    use Touchable, BeforeAndAfterCollection, HasConfig;

    protected $form;

    public function __construct($items = [])
    {
        if (func_num_args() > 0) {
            $this->touch();
        }

        parent::__construct($items);
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $value);

        $this->touch();
    }

    public function offsetUnset($key)
    {
        parent::offsetUnset($key);

        $this->touch();
    }

    public function offsetGet($key)
    {
        return $this->resolve($key, parent::offsetGet($key));
    }

    public function resolve($key, $button)
    {
        if (is_string($button)) {
            $button = ['label' => $button];
        } elseif (is_callable($button)) {
            $button = call_user_func($button, $this);
        }

        if ($button === false) {
            return false;
        }

        if (! is_array($button)) {
            $button = [];
        }

        $stack = [
            $this->config("buttons.{$key}", []),
            $this->config('buttons.default', []),
            [
                'key' => strval($key),
                'label' => strval($key),
                'type' => $key === 'submit' ? 'submit' : 'button',
            ],
        ];

        foreach ($stack as $arr) {
            $button = $button + (array)(is_callable($arr) ? call_user_func($arr, $this) : $arr);
        }

        if (! empty($button['confirm'])) {
            $button['confirm'] = app('translator')->trans(strval($button['confirm']));
        }

        $button['label'] = app('translator')->trans($button['label']);

        return $button;
    }

    public function resolved()
    {
        return collect($this->items)->map(function ($button, $key) {
            return $this->resolve($key, $button);
        })->filter();
    }

    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }
}