<?php

namespace Dukhanin\Panel\Features;

trait DisableAndEnable
{

    protected $disabledKey;


    protected static function routesFeatureDisableAndEnable(array $options = null)
    {
        app('router')->get('enable/{id}', "{$options['class']}@enable")->name($options['as'] ? "{$options['as']}.enable" : null);
        app('router')->get('disable/{id}', "{$options['class']}@disable")->name($options['as'] ? "{$options['as']}.disable" : null);
        app('router')->post('groupEnable', "{$options['class']}@groupEnable")->name($options['as'] ? "{$options['as']}.groupEnable" : null);
        app('router')->post('groupDisable', "{$options['class']}@groupDisable")->name($options['as'] ? "{$options['as']}.groupDisable" : null);
    }


    function initFeatureDisableAndEnable()
    {
        $this->initDisabledKey();

        $this->modelActions['disable'] = $this->config('actions.disable');

        $this->groupActions['group-enable'] = $this->config('actions.group-enable');

        $this->groupActions['group-disable'] = $this->config('actions.group-disable');
    }


    public function initDisabledKey()
    {
        $this->disabledKey = 'disabled';
    }


    public function disabledKey()
    {
        if (is_null($this->disabledKey)) {
            $this->initDisabledKey();
        }

        return $this->disabledKey;
    }


    public function enable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('enable', $model);

        $model->{$this->disabledKey()} = false;
        $model->save();

        return redirect()->to($this->url());
    }


    public function disable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('disable', $model);

        $model->{$this->disabledKey()} = true;
        $model->save();

        return redirect()->to($this->url());
    }


    public function groupEnable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->{$this->disabledKey()} = false;
            $model->save();
        }

        return redirect()->to($this->url());
    }


    public function groupDisable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->{$this->disabledKey()} = true;
            $model->save();
        }

        return redirect()->to($this->url());
    }


    public function applyEachRowDisabled(&$row)
    {
        if ($row['model']->{$this->disabledKey()}) {
            html_tag_add_class($row, 'inactive');
        }
    }
}