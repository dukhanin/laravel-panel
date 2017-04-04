<?php

namespace Dukhanin\Panel\Traits;

use Dukhanin\Panel\Files\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait UploadsFiles
{
    protected $directory;

    abstract public function upload();

    protected function initDirectory()
    {
        $this->directory = request()->input('directory');
        upload()->makeDirectoryIfNotExists($this->directory);
    }

    protected function getDirectory()
    {
        if (is_null($this->directory)) {
            $this->initDirectory();
        }

        return $this->directory;
    }

    protected function getUploadedFiles()
    {
        $files = request()->file('file');

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        return collect($files)->map(function ($uploadedFile) {
            $file = new File;
            $file->setBaseFile($uploadedFile);

            return $file;
        });
    }

    protected function uploadFileToDirectory($file)
    {
        $uploadPath = upload()->path($this->getDirectory());

        $fileBasename = $file->getBaseFile() instanceof UploadedFile ? $file->getClientOriginalName() : $file->getBasename();

        $this->renameIfExists($uploadPath, $fileBasename);

        $file->move($uploadPath.'/'.$fileBasename);

        chmod($file->getPath(), 0644); // @todo do smth with

        return true;
    }

    protected function renameIfExists($uploadPath, &$fileBasename)
    {
        $fileName = pathinfo($fileBasename, PATHINFO_FILENAME);
        $fileExt = pathinfo($fileBasename, PATHINFO_EXTENSION);
        $counter = 0;

        while (file_exists($uploadPath.'/'.$fileBasename)) {
            $fileBasename = $fileName.'('.(++$counter).')';

            if ($fileExt) {
                $fileBasename .= '.'.$fileExt;
            }
        }
    }
}
