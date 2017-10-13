<?php

namespace Dukhanin\Panel\Features;

trait Edit
{
    protected static function routesForEdit(array $options = null)
    {
        app('router')->get('edit/{id}', static::routeAction('edit'))->name('edit');
        app('router')->post('edit/{id}', static::routeAction('updateModel'))->name('updateModel');
    }

    public function initFeatureEdit()
    {
        $this->modelActions()->put('edit');

        if (method_exists($this, 'show') && $this->allows('edit', $this->model())) {
            $this->show()->buttons()->put('edit', [
                'url' => $this->urlTo('edit', [$this->model(), '_show' => true]),
            ]);
        }
    }

    public function edit()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('edit', $this->model);

        $form = $this->form()->setModel($this->model);

        $this->apply($form, ['*'], 'form');

        return $form->view();
    }

    public function updateModel()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('edit', $this->model);

        $form = $this->form()->setModel($this->model);

        $this->apply($form, ['*'], 'form');

        return $form->handle($this->afterSave());
    }
}