<?php

namespace Dukhanin\Panel\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class PanelServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();

        $this->publishMigrations();

        $this->publishRoutes();

        $this->publishControllers();

        $this->publishPolicies();

        $this->publishLang();

        $this->publishViews();

        $this->publishAssets();

        $this->publishSample();

        $this->loadViews();

        $this->loadHelpers();
    }


    public function register()
    {
    }


    protected function publishAssets()
    {
        $this->publishes([
            $this->path('public/assets/filemanager') => public_path('assets/filemanager'),
            $this->path('public/assets/tinymce') => public_path('assets/tinymce'),
            $this->path('public/assets/panel-bootstrap') => public_path('assets/panel-bootstrap'),
            $this->path('public/assets/panel-inspinia') => public_path('assets/panel-inspinia')
        ], 'public');
    }


    protected function publishConfig()
    {
        $this->publishes([
            $this->path('config/panel.php') => config_path('/panel.php'),
            $this->path('config/upload.php') => config_path('/upload.php'),
            $this->path('config/wysiwyg.php') => config_path('/wysiwyg.php'),
            $this->path('config/files.php') => config_path('/files.php'),
        ], 'config');
    }


    protected function publishMigrations()
    {
        $this->publishes([
            $this->path('database/migrations/2016_08_14_101727_create_files_table.php') => database_path('migrations/2016_08_14_101727_create_files_table.php'),
        ], 'migrations');
    }


    protected function publishRoutes()
    {
        $this->publishes([
            $this->path('routes/panel.php') => base_path('routes/panel.php'),
        ], 'routes');
    }


    protected function publishControllers()
    {
        $this->publishes([
            $this->path('app/Http/Controllers/Panel/AbstractUploadController.php') => app_path('Http/Controllers/Panel/AbstractUploadController.php'),
            $this->path('app/Http/Controllers/Panel/PanelTinymceUploadController.php') => app_path('Http/Controllers/Panel/PanelTinymceUploadController.php'),
            $this->path('app/Http/Controllers/Panel/PanelUploadController.php') => app_path('Http/Controllers/Panel/PanelUploadController.php'),
        ], 'routes');
    }

    protected function publishPolicies()
    {
        $this->publishes([
            $this->path('app/Policies/UserPolicy.php') => app_path('Policies/UserPolicy.php'),
        ], 'routes');
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
            $this->path('resources/views/panel-inspinia') => resource_path('views/panel-inspinia')
        ], 'views');
    }


    protected function publishSample()
    {
        $input = new ArgvInput(isset($_SERVER['argv']) ? $_SERVER['argv'] : []);
        $publishWithSample = $input->hasParameterOption('--tag=sample');

        if (!$publishWithSample) {
            return;
        }

        $this->publishes([
            $this->path('app/Http/Controllers/Sample/') => app_path('Http/Controllers/Sample/'),
            $this->path('app/Sample') => app_path('Sample/'),
            $this->path('public/assets/inspinia/') => public_path('assets/inspinia/'),
            $this->path('public/upload/') => public_path('upload'),
            $this->path('config/panel-inspinia.php') => config_path('panel-inspinia.php'),
            $this->path('config/panel-bootstrap.php') => config_path('panel-bootstrap.php'),
            $this->path('database/migrations/2016_10_12_065204_sample_products.php') => database_path('migrations/2016_10_12_065204_sample_products.php'),
            $this->path('database/migrations/2016_10_12_065204_sample_sections.php') => database_path('migrations/2016_10_12_065204_sample_sections.php'),
            $this->path('database/seeds') => database_path('/seeds'),
            $this->path('routes/sample.php') => base_path('routes/sample.php'),
        ], 'sample');
    }


    protected function loadViews()
    {
        $this->loadViewsFrom($this->path('src/views'), 'panel');
    }


    protected function loadHelpers()
    {
        $this->app->singleton('upload', function ($app) {
            return new \Dukhanin\Panel\Files\UploadHelper;
        });
    }


    protected function path($path)
    {
        return __DIR__ . '/../../' . ltrim($path, '/');
    }
}
