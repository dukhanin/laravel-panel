<?php
namespace Dukhanin\Panel\Traits;

use Illuminate\Database\Eloquent\Collection;
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Files as PanelFile;

trait CastsFiles
{
    protected $castsFileCached = [];

    protected $castsFilesCached = [];

    protected function castAttribute($key, $value)
    {
        switch ($this->getCastType($key)) {
            case 'file':
            case 'image':
                if (is_null($value)) {
                    return $value;
                }

                if (! isset($this->castsFileCached[$key])) {
                    return $this->castsFileCached[$key] = $this->asFile($value);
                }

                return $this->castsFileCached[$key];
            case 'files':
            case 'images':
                if (! isset($this->castsFilesCached[$key])) {
                    return $this->castsFilesCached[$key] = $this->asFiles($value);
                }

                return $this->castsFilesCached[$key];
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

        $class = $this->castFileClass();

        if ($value instanceof $class) {
            return $value;
        }

        return $class::find($value instanceof PanelFile ? $value->getKey() : $value);
    }

    protected function asFiles($value)
    {
        if ($value instanceof Collection) {
            return $value;
        }

        if (is_string($value)) {
            $value = $this->fromJson($value);
        } elseif (is_array($value)) {
            $value = array_map('intval', $value);
        }

        return ($this->castFileClass())::findManyOrdered($value);
    }

    protected function fromFile($value)
    {
        if ($value instanceof PanelFile) {
            return $value->getKey();
        }

        return $value;
    }

    protected function fromFiles($value)
    {
        if ($value instanceof Collection) {
            $value = $value->modelKeys();
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

    protected function castFileClass()
    {
        if (property_exists($this, 'castFileClass') && $this->castFileClass) {
            return $this->castFileClass;
        }

        return File::class;
    }
}