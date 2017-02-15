<?php

namespace Dukhanin\Panel\Collections;

use Dukhanin\Support\ResolvedCollection;

class RoutesMetaCollection extends ResolvedCollection
{

    protected $class;

    protected $replacements = [ ];


    public function setClass($class)
    {
        $this->class = $class;
    }


    public function resolvedFor(array $options)
    {
        return $this->map(function ($meta) use ($options) {
            $options = $options + [
                    'as'         => null,
                    'prefix'     => null,
                    'middleware' => null,
                    'class'      => $this->class
                ];

            $meta['action'] = $this->action($meta, $options);
            $meta['name']   = $this->name($meta, $options);
            $meta['uri']    = $this->uri($meta, $options);

            return array_except($options, [ 'as', 'prefix' ]) + $meta;
        });
    }


    public function resolveItemOnSet($key, $meta)
    {
        if (is_callable($meta)) {
            return $meta;
        }

        if ( ! is_array($meta)) {
            $meta = [ ];
        }

        $meta = array_merge([
            'methods' => [ ],
            'uri'     => null,
            'action'  => null,
        ], $meta);

        return $meta;
    }


    public function match($methods, $uri, $action = null)
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }


    public function get($uri, $action = null)
    {
        return $this->addRoute([ 'GET', 'HEAD' ], $uri, $action);
    }


    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action);
    }


    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }


    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }


    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }


    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }


    public function any($uri, $action = null)
    {
        $verbs = [ 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE' ];

        return $this->addRoute($verbs, $uri, $action);
    }


    protected function addRoute($methods, $uri, $action)
    {
        $this->push([
            'methods' => $methods,
            'uri'     => $uri,
            'action'  => $action
        ]);

        return $this;
    }


    protected function uri($meta, $options)
    {
        if ( ! $options['prefix']) {
            return strtr($meta['uri'], $this->replacements);
        }

        return strtr(trim(trim($options['prefix'], '/') . '/' . $meta['uri'], ''), $this->replacements);
    }


    protected function action($meta, $options)
    {
        if ( ! str_contains($meta['action'], '@')) {
            return '\\' . ltrim($this->class, '\\') . '@' . $meta['action'];
        }

        return $meta['action'];
    }


    protected function name($meta, $options)
    {
        if ( ! $options['as']) {
            return false;
        }

        return rtrim($options['as'], '.') . '.' . explode('@', $meta['action'])[1];
    }

}