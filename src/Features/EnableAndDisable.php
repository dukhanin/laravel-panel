<?php

namespace Dukhanin\Panel\Features;

trait EnableAndDisable
{
    public function enabledKey()
    {
        return 'enabled';
    }

    public function enabledKeyInversion()
    {
        return false;
    }

    protected static function routesForEnableAndDisable(array $options = null)
    {
        app('router')->get('enable/{id}', static::routeAction('enable'))->name('enable');

        app('router')->get('disable/{id}', static::routeAction('disable'))->name('disable');

        app('router')->post('group-enable', static::routeAction('groupEnable'))->name('groupEnable');

        app('router')->post('group-disable', static::routeAction('groupDisable'))->name('groupDisable');
    }

    function initFeatureEnableAndDisable()
    {
        $this->modelActions()->put('enable', function ($panel, $model) {
            $key = method_exists($panel, 'enabledKey') ? $panel->enabledKey() : 'enabled';
            $inversion = method_exists($panel, 'enabledKeyInversion') ? $panel->enabledKeyInversion() : false;

            return config('panel.actions.' . ($model->{$key} == ! $inversion ? 'disable' : 'enable'));
        });

        $this->groupActions()->put('group-enable');

        $this->groupActions()->put('group-disable');

        if (method_exists($this, 'show') && $this->allows($action = $this->model()->{$this->enabledKey()} == ! $this->enabledKeyInversion() ? 'disable' : 'enable', $this->model())) {
            $this->show()->buttons()->put($action, [
                'url' => $this->urlTo($action, [$this->model(), '_show' => true]),
            ]);
        }
    }

    public function enable()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('enable', $this->model);

        $this->model->{$this->enabledKey()} = $this->enabledKeyInversion() ? false : true;
        $this->model->save();

        return redirect()->to(url()->previous($this->url()));
    }

    public function disable()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('disable', $this->model);

        $this->model->{$this->enabledKey()} = $this->enabledKeyInversion() ? true : false;
        $this->model->save();

        return redirect()->to(url()->previous($this->url()));
    }

    public function groupEnable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-enable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey()} = $this->enabledKeyInversion() ? false : true;
            $model->save();
        }

        return redirect()->to($this->url());
    }

    public function groupDisable()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-disable', $group);

        foreach ($group as $model) {
            $model->{$this->enabledKey()} = $this->enabledKeyInversion() ? true : false;
            $model->save();
        }

        return redirect()->to($this->url());
    }

    public function applyEachRowDisabled(&$row)
    {
        if (! $row['model']->{$this->enabledKey()} == ! $this->enabledKeyInversion()) {
            html_tag_add_class($row, 'inactive');
        }
    }
}