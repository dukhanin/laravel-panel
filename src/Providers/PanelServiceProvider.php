<?php

namespace Dukhanin\Panel\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class PanelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('upload', function ($app) {
            return new \Dukhanin\Panel\Files\UploadHelper;
        });
    }

    public function boot()
    {
        $this->mergeConfigFrom($this->path('config/files.php'), 'files');

        $this->publishConfig();

        $this->publishControllers();

        $this->publishMigrations();

        $this->publishRoutes();

        $this->publishLang();

        $this->publishAssets();

        $this->publishInspinia();

        $this->loadViews();
    }

    protected function publishConfig()
    {
        $this->publishes([
            $this->path('config/panel.php') => config_path('/panel.php'),
            $this->path('config/upload.php') => config_path('/upload.php'),
            $this->path('config/wysiwyg.php') => config_path('/wysiwyg.php'),
        ], 'config');
    }

    protected function publishControllers()
    {
        $this->publishes([
            $this->path('app/Http/Controllers/Panel/PanelTinymceUploadController.php') => app_path('Http/Controllers/Panel/PanelTinymceUploadController.php'),
            $this->path('app/Http/Controllers/Panel/PanelFormUploadController.php') => app_path('Http/Controllers/Panel/PanelFormUploadController.php'),
        ], 'controllers');
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

    protected function publishLang()
    {
        $this->publishes([
            $this->path('resources/lang/en/panel.php') => resource_path('lang/en/panel.php'),
            $this->path('resources/lang/ru/panel.php') => resource_path('lang/ru/panel.php'),
        ], 'lang');
    }

    protected function publishAssets()
    {
        $this->publishes([
            $this->path('public/assets/filemanager') => public_path('assets/filemanager'),
        ], 'filemanager');

        $this->publishes([
            $this->path('public/assets/tinymce') => public_path('assets/tinymce'),
        ], 'tinymce');

        $this->publishes([
            $this->path('public/assets/panel-inspinia') => public_path('assets/panel-inspinia'),
        ], 'assets');
    }

    protected function publishInspinia()
    {
        $input = new ArgvInput(isset($_SERVER['argv']) ? $_SERVER['argv'] : []);
        $publishInspinia = $input->hasParameterOption('--tag=inspinia');

        if (! $publishInspinia) {
            return;
        }

        $this->publishes([
            $this->path('public/assets/inspinia/') => public_path('assets/inspinia/'),
        ], 'inspinia');
    }

    protected function loadViews()
    {
        $this->loadViewsFrom($this->path('resources/views'), 'panel');
    }

    protected function path($path)
    {
        return __DIR__.'/../../'.ltrim($path, '/');
    }
}
