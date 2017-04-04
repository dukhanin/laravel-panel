<?php

namespace Dukhanin\Panel\Features;

trait DisableAndEnable
{
    protected $disabledKey;

    protected static function routesForDisableAndEnable(array $options = null)
    {
        app('router')->get('enable/{id}', '\\'.static::class.'@enable')->name('enable');

        app('router')->get('disable/{id}', '\\'.static::class.'@disable')->name('disable');

        app('router')->post('group-enable', '\\'.static::class.'@groupEnable')->name('groupEnable');

        app('router')->post('group-disable', '\\'.static::class.'@groupDisable')->name('groupDisable');
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

    public function enable()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('enable', $model);

        $model->{$this->disabledKey()} = false;
        $model->save();

        return redirect()->to($this->url());
    }

    public function disable()
    {
        $model = $this->findModelOrFail($this->parameter('id'));

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