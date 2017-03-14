<?php
namespace Dukhanin\Panel\Files;

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

        if ( ! app('files')->exists($directory)) {
            app('files')->makeDirectory($directory, 0755, true);
        }
    }
}
