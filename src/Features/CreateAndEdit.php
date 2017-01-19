<?php

namespace Dukhanin\Panel\Features;

use Dukhanin\Panel\PanelForm;

trait CreateAndEdit
{

    protected $form;


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

        $this->form->addCancelButton([ 'url' => $this->getUrl() ]);
        $this->form->addSubmitButton([ 'url' => $this->getUrl() ]);
        $this->form->addApplyButton();
    }


    public function getForm()
    {
        if (is_null($this->form)) {
            $this->initForm();
            $this->setupForm();
        }

        return $this->form;
    }


    public function actionEdit($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('edit', $model);

        return $this->getForm()->setModel($model)->execute();
    }


    public function actionCreate()
    {
        $model = $this->newModel();

        $this->authorize('create', $model);

        if (in_array(Sort::class, class_uses_recursive(get_class($this)))) {
            $this->getForm()->succeed(function () use ($model) {
                if ($this->sortNewModelToTop) {
                    $this->sortModelToTop($model);
                } else {
                    $this->sortModelToBottom($model);
                }
            });
        }

        return $this->getForm()->setModel($model)->execute();
    }

}