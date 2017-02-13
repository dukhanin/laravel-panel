<?php

namespace Dukhanin\Panel\Features;

trait EnableAndDisable
{

    protected $enabledKey;


    public static function routesFeatureEnableAndDisable($className)
    {
        app('router')->get('enable/{id}', "{$className}@enable");
        app('router')->get('disable/{id}', "{$className}@disable");
        app('router')->post('groupEnable', "{$className}@groupEnable");
        app('router')->post('groupDisable', "{$className}@groupDisable");
    }


    function initFeatureEnableAndDisable()
    {
        $this->initEnabledKey();

        $this->modelActions['enable'] = $this->config('actions.enable');

        $this->groupActions['group-enable'] = $this->config('actions.group-enable');

        $this->groupActions['group-disable'] = $this->config('actions.group-disable');
    }


    public function initEnabledKey()
    {
        $this->enabledKey = 'enabled';
    }


    public function enabledKey()
    {
        if (is_null($this->enabledKey)) {
            $this->initEnabledKey();
        }

        return $this->enabledKey;
    }


    public function enable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('enable', $model);

        $model->{$this->enabledKey()} = true;
        $model->save();

        return redirect()->to($this->url());
    }


    public function disable($primaryKey)
    {
        $model = $this->findModelOrFail($primaryKey);

        $this->authorize('disable', $model);

        $model->{$this->enabledKey()} = false;
        $model->save();

        return redirect()->to($this->url());
    }


    public function groupEnable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey()} = true;
            $model->save();
        }

        return redirect()->to($this->url());
    }


    public function groupDisable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey()} = false;
            $model->save();
        }

        return redirect()->to($this->url());
    }


    public function applyEachRowDisabled(&$row)
    {
        if ( ! $row['model']->{$this->enabledKey()}) {
            html_tag_add_class($row, 'inactive');
        }
    }
}