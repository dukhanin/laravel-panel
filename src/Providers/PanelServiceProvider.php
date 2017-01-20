<?php

namespace Dukhanin\Panel\Providers;

use Illuminate\Support\ServiceProvider;

use Symfony\Component\Console\Input\ArgvInput;

class PanelServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();

        $this->publishLang();

        $this->publishViews();

        $this->publishAssets();

        $this->publishSample();

        $this->loadViews();
    }


    public function register()
    {
    }


    protected function pusblish()
    {


    }


    protected function publishAssets()
    {
        $this->publishes([
            $this->path('public/assets/') => public_path('assets/'),
        ], 'public');
    }


    protected function publishConfig()
    {
        $this->publishes([
            $this->path('config/') => config_path('/'),
        ], 'config');
    }


    protected function publishLang()
    {
        $this->publishes([
            $this->path('resources/lang/') => resource_path('lang/'),
        ], 'lang');
    }


    protected function publishViews()
    {
        $this->publishes([
            $this->path('resources/views/') => resource_path('views/'),
        ], 'views');
    }


    protected function publishSample()
    {
        $publishWithSample = (new ArgvInput)->hasParameterOption('--tag=sample');

        if ( ! $publishWithSample) {
            return;
        }

        $this->publishes([
            $this->path('sample/app/')      => app_path('/'),
            $this->path('sample/database/') => database_path('/'),
            $this->path('sample/routes/')   => base_path('routes/'),
        ], 'sample');
    }


    protected function loadViews()
    {
        $this->loadViewsFrom($this->path('src/views'), 'panel');
    }


    protected function path($path)
    {
        return __DIR__ . '/../../' . ltrim($path, '/');
    }
}
