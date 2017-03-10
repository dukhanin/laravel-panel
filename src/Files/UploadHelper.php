<?php
namespace Dukhanin\Panel\Files;

class UploadHelper
{

    public function __construct()
    {
        $this->makeDirectoryIfNotExists();
    }


    public function path($append = null)
    {
        return config('upload.path') . $this->append($this->subdir()) . $this->append($append);
    }


    public function url($append = null)
    {
        return config('upload.url') . $this->append($this->subdir()) . $this->append($append);
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
            app('files')->makeDirectory($directory);
        }
    }
}
