<?php

namespace Dukhanin\Panel\Traits;

use Dukhanin\Panel\Files\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait UploadsFiles
{
    protected $directory, $fileType;

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

    protected function initFileType()
    {
        $this->fileType = request()->input('fileType') ?? 'default';
    }

    protected function getFileType()
    {
        if (is_null($this->fileType)) {
            $this->initFileType();
        }

        return $this->fileType;
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

        chmod($file->getPath(), 0644);

        return true;
    }

    protected function getValidExtensions()
    {
        $fileType = $this->getFileType();

        $types = config("files.types");

        $validExtensions = [];

        if (isset($types[$fileType]['extensions'])) {
            $validExtensions = $types[$fileType]['extensions'];
        } else {
            foreach ($types as $key => $type) {
                $validExtensions = array_merge($validExtensions, $type['extensions']);
            }
        }

        return $validExtensions;
    }

    protected function validateFilesExt($files, $extensions) : bool
    {
        if ($extensions)
            foreach ($files as $file) {
                if (!in_array($file->clientExtension(), $extensions))
                    return false;
            }

        return true;
    }

    protected function renameIfExists($uploadPath, &$fileBasename)
    {
        // без задания локали pathinfo() обрезает начало в имени файла на латинице
        $oldLocale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'ru_RU.utf8');

        $fileName = pathinfo($fileBasename, PATHINFO_FILENAME);
        $fileExt = pathinfo($fileBasename, PATHINFO_EXTENSION);
        $counter = 0;

        setlocale(LC_CTYPE, $oldLocale);

        while (file_exists($uploadPath.'/'.$fileBasename)) {
            $fileBasename = $fileName.'('.(++$counter).')';

            if ($fileExt) {
                $fileBasename .= '.'.$fileExt;
            }
        }
    }
}
