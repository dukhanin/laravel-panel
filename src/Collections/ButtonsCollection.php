<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\ResolvedCollection;

class ButtonsCollection extends ResolvedCollection
{

    protected $panel;


    public function setPanel($panel)
    {
        $this->panel = $panel;
    }


    public function resolveItemOnSet($key, $button)
    {
        if (is_callable($button)) {
            $button = call_user_func($button, $this->panel);
        }

        $_button = array_merge($this->panel->config('buttons.default', [ ]),
            $this->panel->config("buttons.{$key}", [ ]), (array) $button, [ 'key' => $key ]);

        if (empty( $_button['label'] )) {
            $_button['label'] = $key;
        }

        if (isset( $button['url'] )) {
            $_button['url'] = $button['url'];
        } else {
            $_button['url'] = $this->panel->submitUrl();
        }

        if (isset( $button['type'] )) {
            $_button['type'] = $button['type'];
        } elseif ( ! isset( $_button['type'] )) {
            $_button['type'] = $key === 'submit' ? 'submit' : 'button';
        }

        $_button['label'] = trans($_button['label']);

        return $_button;
    }


    public function put($key, $value = null)
    {
        return parent::put($key, $value);
    }

}