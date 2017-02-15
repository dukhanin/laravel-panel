<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelForm;
use Dukhanin\Panel\Traits\PanelTreeTrait;

trait CreateAndEdit
{

    protected $form;


    protected static function routesForCreateAndEdit(array $options = null)
    {
        if (class_uses(static::class, PanelTreeTrait::class)) {
            static::routesMeta()->match([ 'get', 'post' ], 'create/{into?}', 'create');
        } else {
            static::routesMeta()->match([ 'get', 'post' ], 'create', 'create');
        }

        static::routesMeta()->match([ 'get', 'post' ], 'edit/{id}', 'edit');
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

        $this->form->buttons()->put('cancel', [ 'url' => $this->url() ]);
        $this->form->buttons()->put('submit');
        $this->form->buttons()->put('apply');

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


    public function edit()
    {
        $model = $this->findModelOrFail($this->route('id'));

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
        $this->form()->succeed(function ($form) {
            if ( ! $form->data('_apply')) {
                return;
            }

            $url = request()->fullUrl();

            if ($form->model()->wasRecentlyCreated) {
                $url = $this->urlTo('edit', $form->model());
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