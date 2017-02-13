<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelForm;

trait CreateAndEdit
{

    protected $form;


    public static function routesFeatureCreateAndEdit($className)
    {
        app('router')->match(['get', 'post'], 'create', "{$className}@create");
        app('router')->match(['get', 'post'], 'edit/{id}', "{$className}@edit");
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
        $this->form->configSet(null, $this->config());

        $this->form->addButton('cancel', [ 'url' => $this->url() ]);
        $this->form->addButton('submit');
        $this->form->addButton('apply');

        $this->redirectToFormAfterApply();

        $this->redirectToListAfterSave();
    }


    public function form()
    {
        if (is_null($this->form)) {
            $this->initForm();
            $this->setupForm();
        }

        return $this->form;
    }


    public function edit($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('edit', $model);

        return $this->form()->setModel($model)->execute();
    }


    public function create()
    {
        $model = $this->newModel();

        $this->authorize('create', $model);

        return $this->form()->setModel($model)->execute();
    }


    private function redirectToFormAfterApply()
    {
        $this->form()->succeed(function () {
            if ( ! $this->input('_apply')) {
                return;
            }

            $url = request()->fullUrl();

            if ($this->form()->model()->wasRecentlyCreated) {
                $url = urlbuilder($url)->pop('/create')->append([
                    'edit',
                    $this->form()->model()->getKey()
                ])->compile();
            }

            abort(301, '', [ 'Location' => $url ]);
        }, -1);
    }


    private function redirectToListAfterSave()
    {
        $this->form()->succeed(function () {
            abort(301, '', [ 'Location' => $this->url() ]);
        }, -2);
    }
}