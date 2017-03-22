<?php

namespace Dukhanin\Panel\Features;

trait EnableAndDisable
{

    protected $enabledKey;


    protected static function routesForEnableAndDisable(array $options = null)
    {
        app('router')->get('enable/{id}', '\\' . static::class . '@enable');

        app('router')->get('disable/{id}', '\\' . static::class . '@disable');

        app('router')->post('group-enable', '\\' . static::class . '@groupEnable');

        app('router')->post('group-disable', '\\' . static::class . '@groupDisable');
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


    public function enable()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('enable', $model);

        $model->{$this->enabledKey()} = true;
        $model->save();

        return redirect()->to($this->url());
    }


    public function disable()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

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