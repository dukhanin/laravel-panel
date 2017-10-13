<?php

namespace Dukhanin\Panel\Tests;

use Intervention\Image\ImageManager;

trait CreatesApplication
{
    protected $app;

    public function createApplication()
    {
        $this->app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $this->app->useEnvironmentPath(__DIR__.'/../');

        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->loadConfig();

        $this->configureInterventionImage();

        $this->migrate();

        return $this->app;
    }

    protected function loadConfig()
    {
        $this->app->config->set('files', require __DIR__.'/../config/files.php');
        $this->app->config->set('upload', require __DIR__.'/../config/upload.php');
        $this->app->config->set('upload.path', __DIR__.'/../storage/app/public/tests');
        $this->app->config->set('upload.url', '/tests');
    }

    protected function configureInterventionImage()
    {
        $this->app->config->set('image', require __DIR__.'/../../../intervention/image/src/config/config.php');

        $this->app->singleton('image', function ($app) {
            return new ImageManager($app['config']->get('image'));
        });
    }

    protected function migrate()
    {
        $migrator = $this->app->make('migrator');

        if (! ($repository = $migrator->getRepository())->repositoryExists()) {
            $repository->createRepository();
        }

        $migrator->run([__DIR__.'/../database/migrations']);
    }
}
