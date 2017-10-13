<?php

namespace Dukhanin\Panel\Features;

trait LockAndUnlock
{
    public function lockedKey()
    {
        return 'locked';
    }

    public function lockedKeyInversion()
    {
        return false;
    }

    protected static function routesForLockAndUnlock(array $options = null)
    {
        app('router')->get('unlock/{id}', '\\'.static::class.'@unlock')->name('unlock');

        app('router')->get('lock/{id}', '\\'.static::class.'@lock')->name('lock');

        app('router')->post('group-unlock', '\\'.static::class.'@groupUnlock')->name('groupUnlock');

        app('router')->post('group-lock', '\\'.static::class.'@groupLock')->name('groupLock');
    }

    function initFeatureLockAndUnlock()
    {
        $this->modelActions()->put('lock', function ($panel, $model) {
            $key = method_exists($panel, 'lockedKey') ? $panel->lockedKey() : 'locked';
            $inversion = method_exists($panel, 'lockedKeyInversion') ? $panel->lockedKeyInversion() : false;

            return config('panel.actions.' . ($model->{$key} == ! $inversion ? 'lock' : 'unlock'));
        });

        $this->groupActions()->put('group-unlock');

        $this->groupActions()->put('group-lock');

        if (method_exists($this, 'show') && $this->allows($action = $this->model()->{$this->lockedKey()} == !$this->lockedKeyInversion() ? 'unlock' : 'lock', $this->model())) {
            $this->show()->buttons()->put($action, [
                'url' => $this->urlTo($action, [$this->model(), '_show' => true]),
            ]);
        }
    }

    public function unlock()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('unlock', $this->model);

        $this->model->{$this->lockedKey()} = $this->lockedKeyInversion() ? true : false;
        $this->model->save();

        return redirect()->to($this->url());
    }

    public function lock()
    {
        $this->model = $this->findModelOrFail($this->parameter('id'));

        $this->authorize('lock', $this->model);

        $this->model->{$this->lockedKey()} = $this->lockedKeyInversion() ? false : true;
        $this->model->save();

        return redirect()->to($this->url());
    }

    public function groupUnlock()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-unlock', $group);

        foreach ($group as $model) {
            $model->{$this->lockedKey()} = $this->lockedKeyInversion() ? true : false;
            $model->save();
        }

        return redirect()->to($this->url());
    }

    public function groupLock()
    {
        $group = $this->findModelsOrFail($this->input('group'));

        $this->authorize('group-lock', $group);

        foreach ($group as $model) {
            $model->{$this->lockedKey()} = $this->lockedKeyInversion() ? false : true;
            $model->save();
        }

        return redirect()->to($this->url());
    }

    public function applyEachRowLocked(&$row)
    {
        if ($row['model']->{$this->lockedKey()} == ! $this->lockedKeyInversion()) {
            html_tag_add_class($row, 'inactive');
        }
    }
}