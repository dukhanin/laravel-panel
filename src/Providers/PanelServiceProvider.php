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
            $this->path('public/assets/panel-bootstrap') => public_path('assets/panel-bootstrap'),
            $this->path('public/assets/panel-inspinia')  => public_path('assets/panel-inspinia')
        ], 'public');
    }


    protected function publishConfig()
    {
        $this->publishes([
            $this->path('config/panel.php') => config_path('/panel.php'),
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
            $this->path('resources/views/panel-bootstrap') => resource_path('views/panel-bootstrap'),
            $this->path('resources/views/panel-inspinia')  => resource_path('views/panel-inspinia')
        ], 'views');
    }


    protected function publishSample()
    {
        $input             = new ArgvInput(isset( $_SERVER['argv'] ) ? $_SERVER['argv'] : [ ]);
        $publishWithSample = $input->hasParameterOption('--tag=sample');

        if ( ! $publishWithSample) {
            return;
        }

        $this->publishes([
            $this->path('app/Http/Controllers/Sample/') => app_path('Http/Controllers/Sample/'),
            $this->path('app/Sample')                   => app_path('Sample/'),
            $this->path('public/assets/inspinia/')      => public_path('assets/inspinia/'),
            $this->path('config/panel-inspinia.php')    => config_path('panel-inspinia.php'),
            $this->path('config/panel-bootstrap.php')   => config_path('panel-bootstrap.php'),
            $this->path('database/')                    => database_path('/'),
            $this->path('routes/')                      => base_path('routes/'),
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
