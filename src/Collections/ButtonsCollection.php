<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\Traits\BeforeAndAfterCollection;
use Dukhanin\Support\Traits\Toucheble;
use Illuminate\Support\Collection;

class ButtonsCollection extends Collection
{
    use Toucheble, BeforeAndAfterCollection;

    protected $form;

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
            $button['label'] = $button;
        } elseif (is_callable($button)) {
            $button = call_user_func($button, $this->form);
        }

        if (! is_array($button)) {
            $button = [];
        }

        $button = $button + $this->form->config("buttons.{$key}", []) + $this->form->config('buttons.default', []) + [
                'key' => strval($key),
                'label' => strval($key),
                'type' => $key === 'submit' ? 'submit' : 'button',
            ];

        if (! isset($button['url'])) {
            $button['url'] = $this->form->submitUrl();
        }

        $button['label'] = trans($button['label']);

        return $button;
    }

    public function resolved()
    {
        return collect($this->items)->map(function ($button, $key) {
            return $this->resolve($key, $button);
        });
    }

    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }
}