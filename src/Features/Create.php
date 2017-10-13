<?php

namespace Dukhanin\Panel\Features;

trait Create
{
    protected static function routesForCreate(array $options = null)
    {
        app('router')->get('create/{into?}', static::routeAction('create'))->name('create');
        app('router')->post('create/{into?}', static::routeAction('createModel'))->name('createModel');
    }

    public function initFeatureCreate()
    {
        $this->actions['create'] = $this->config('actions.create');
    }

    public function create()
    {
        $this->model = $this->newModel();

        $this->authorize('create', $this->model);

        $form = $this->form()->setModel($this->model);

        $this->apply($form, ['*'], 'form');

        return $form->view();
    }

    public function createModel()
    {
        $this->model = $this->newModel();

        $this->authorize('create', $this->model);

        $form = $this->form()->setModel($this->model);

        $this->apply($form, ['*'], 'form');

        return $form->handle($this->afterSave());
    }
}