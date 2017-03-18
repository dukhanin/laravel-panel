<?php
namespace Dukhanin\Panel\Files;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class UploadHelper
{
    protected $path;

    protected $url;

    public function __construct()
    {
        $this->path = config('upload.path', public_path('upload'));

        $this->url = config('upload.url', '/upload');

        $this->makeDirectoryIfNotExists();
    }


    public function path($append = null)
    {
        return $this->path . $this->append($this->subdir()) . $this->append($append);
    }


    public function url($append = null)
    {
        return $this->url . $this->append($this->subdir()) . $this->append($append);
    }


    public function allowed($arguments = [])
    {
        if (config('upload.unauthorized_upload')) {
            return true;
        }

        return Gate::allows('upload-files');
    }


    public function authorize($arguments = [])
    {
        if (!$this->allowed($arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }


    public function subdir()
    {
        return null;
    }


    protected function append($path)
    {
        $path = strval($path);
        $path = preg_replace('~/+~', '/', $path);
        $path = trim($path, '/');

        return $path ? '/' . $path : '';
    }


    public function makeDirectoryIfNotExists($append = null)
    {
        $directory = $this->path($append);

        if (!app('files')->exists($directory)) {
            app('files')->makeDirectory($directory, 0755, true);
        }
    }
}
