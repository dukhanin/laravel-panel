<?php

namespace Dukhanin\Panel\Tests;

use App\Exceptions\Handler;
use Exception;
use Intervention\Image\ImageServiceProviderLaravel5;
use Illuminate\Support\Facades\File as Filesystem;

trait CreatesApplication
{
    protected $app;

    public function createApplication()
    {
        $this->app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $this->app->useEnvironmentPath(__DIR__.'/../');

        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->app->register(new ImageServiceProviderLaravel5($this->app));

        $this->loadConfig();

        $this->runMigrations();

        return $this->app;
    }

    protected function loadConfig()
    {
        $this->app->setBasePath(realpath(__DIR__.'/../'));

        $this->app->config->set('files', require __DIR__.'/../config/files.php');
        $this->app->config->set('upload', require __DIR__.'/../config/upload.php');
        $this->app->config->set('upload.path', realpath(__DIR__.'/../storage/app/public/storage'));
        $this->app->config->set('upload.url', '/storage');
    }

    protected function runMigrations()
    {
        $migrator = $this->app->make('migrator');

        if (! ($repository = $migrator->getRepository())->repositoryExists()) {
            $repository->createRepository();
        }

        $migrator->run([__DIR__.'/../database/migrations']);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(Handler::class, new class extends Handler
        {
            public function __construct()
            {
            }

            public function report(Exception $exception)
            {
            }

            public function render($request, Exception $exception)
            {
                throw $exception;
            }
        });
    }

    protected function tearDown()
    {
        foreach (Filesystem::glob(config('upload.path').'/*') as $path) {
            if (is_dir($path)) {
                Filesystem::deleteDirectory($path);
            } else {
                Filesystem::delete($path);
            }
        }
    }
}
