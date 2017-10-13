<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelShow;

trait Show
{
    protected $show;

    public function initFeatureShow()
    {
        $this->modelActions()->put('show');
    }

    protected static function routesForShow()
    {
        app('router')->get('show/{id}', static::routeAction('showModel'))->name('showModel');
    }

    public function url(array $apply = [])
    {
        if ($this->backToShow()) {
            return $this->urlTo('showModel', null, ['!BackToShow']);
        }

        return parent::url($apply);
    }

    protected function backToShow()
    {
        return $this->input('_show');
    }

    protected function applyUrlBackToShow($url)
    {
        if ($this->backToShow()) {
            $url->query(['_show' => true]);
        }
    }

    public function initShow()
    {
        $this->show = new PanelShow;
    }

    protected function setupShow()
    {
        $this->show->setConfig(null, $this->config());

        $this->show->buttons()->put('back', [
            'url' => $this->url(),
        ]);
    }

    public function show()
    {
        if (is_null($this->show)) {
            $this->initShow();
            $this->setupShow();
        }

        return $this->show;
    }

    public function showModel()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('show', $this->model);

        $this->show()->setModel($this->model);

        $this->show()->buttons()->put('back', ['url' => $this->url()]);

        return $this->show()->view();
    }
}