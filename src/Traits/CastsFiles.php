<?php
namespace Dukhanin\Panel\Traits;

use Illuminate\Database\Eloquent\Collection;
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Files\FileManager;

trait CastsFiles
{
    protected function castAttribute($key, $value)
    {
        switch ($this->getCastType($key)) {
            case 'file':
            case 'image':
                return $this->asFile($value);
            case 'files':
            case 'images':
                return $this->asFiles($value);
        }

        return parent::castAttribute($key, $value);
    }

    public function setAttribute($key, $value)
    {
        if ($value && $this->isFileAttribute($key)) {
            $value = $this->fromFile($value);
        }

        if ($value && $this->isFilesAttribute($key)) {
            $value = $this->fromFiles($value);
        }

        return parent::setAttribute($key, $value);
    }

    protected function asFile($value)
    {
        if (in_array($value, [null, 0, ''], true)) {
            return null;
        }

        $fileManager = app(FileManager::class);

        $fileClass = $fileManager->getFileClass();

        if ($value instanceof $fileClass) {
            return $value;
        }

        return $fileManager->find($value instanceof File ? $value->getKey() : $value);
    }

    protected function asFiles($value)
    {
        if ($value instanceof Collection) {
            return $value;
        }

        if (is_string($value)) {
            $value = $this->fromJson($value);
        }

        return app(FileManager::class)->findMany($value);
    }

    protected function fromFile($value)
    {
        return $value instanceof File ? $value->getKey() : $value;
    }

    protected function fromFiles($value)
    {
        if ($value instanceof Collection) {
            $value = $value->modelKeys();
        }

        if (is_string($value)) {
            $value = $this->fromJson($value);
        }

        return $this->asJson($value);
    }

    protected function isFileAttribute($key)
    {
        return in_array(array_get($this->getCasts(), $key), ['file', 'image']);
    }

    protected function isFilesAttribute($key)
    {
        return in_array(array_get($this->getCasts(), $key), ['files', 'images']);
    }
}