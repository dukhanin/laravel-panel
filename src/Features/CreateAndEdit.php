<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelForm;

trait CreateAndEdit
{
    protected $form;

    protected static function routesForCreateAndEdit(array $options = null)
    {
        app('router')->get('create/{into?}', static::routeAction('create'))->name('create');
        app('router')->post('create/{into?}', static::routeAction('createModel'))->name('createModel');

        app('router')->get('edit/{id}', static::routeAction('edit'))->name('edit');
        app('router')->post('edit/{id}', static::routeAction('updateModel'))->name('updateModel');
    }

    public function initFeatureCreateAndEdit()
    {
        $this->actions['create'] = $this->config('actions.create');

        $this->modelActions['edit'] = $this->config('actions.edit');
    }

    public function initForm()
    {
        $this->form = new PanelForm;
    }

    public function setupForm()
    {
        $this->form->setConfig(null, $this->config());

        $this->form->buttons()->put('cancel', ['url' => $this->url()]);
        $this->form->buttons()->put('submit');
        $this->form->buttons()->put('apply');
    }

    public function form()
    {
        if (is_null($this->form)) {
            $this->initForm();
            $this->setupForm();
        }

        return $this->form;
    }

    public function edit()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('edit', $model);

        return $this->form()->setModel($model)->view();
    }

    public function updateModel()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('edit', $model);

        $this->form()->setModel($model);

        return $this->form()->handle($this->afterSave());
    }

    public function create()
    {
        $model = $this->newModel();

        $this->authorize('create', $model);

        return $this->form()->setModel($model)->view();
    }

    public function createModel()
    {
        $model = $this->newModel();

        $this->authorize('create', $model);

        $this->form()->setModel($model);

        return $this->form()->handle($this->afterSave());
    }

    public function afterSave()
    {
        return function () {
            if (request()->input('_apply')) {
                return redirect()->to($this->urlTo('edit', ['id' => $this->form()->model()]));
            }

            return redirect()->to($this->url());
        };
    }
}