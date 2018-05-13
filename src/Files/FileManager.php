<?php

namespace Dukhanin\Panel\Files;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FileManager
{
    /**
     * @var array
     */
    protected $loadedFiles = [];

    /**
     * @var string
     */
    protected $fileClass = File::class;

    /**
     * @param integer|string $id
     *
     * @return \Dukhanin\Panel\Files\File
     */
    public function find($id)
    {
        $file = $this->getLoaded($id) ?? $this->load($id);

        if (! $this->isLoaded($id)) {
            $this->setLoaded($id, $file);
        }

        return $file;
    }

    /**
     * @param integer|string $id
     *
     * @throws ModelNotFoundException
     *
     * @return \Dukhanin\Panel\Files\File
     */
    public function findOrFail($id)
    {
        if (empty($model = $this->get($id))) {
            throw (new ModelNotFoundException)->setModel($this->getFileClass());
        }

        return $model;
    }

    /**
     * @param mixed $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids)
    {
        $ids = array_map('intval', is_array($ids) ? $ids : [$ids]);

        $files = collect($ids)->map(function ($id) {
            return $this->getLoaded($id) ?? $id;
        });

        $idsToLoad = $files->filter(function ($id) {
            return is_integer($id);
        });

        $loaded = ($this->getFileClass())::findMany($idsToLoad->toArray());

        return new Collection($files->map(function ($id) use ($loaded) {
            return is_scalar($id) ? $loaded->find($id) : $id;
        })->filter());
    }

    /**
     * @param integer|string $id
     *
     * @return \Dukhanin\Panel\Files\File
     */
    public function load($id)
    {
        $class = $this->getFileClass();

        return $class::find($id);
    }

    /**
     * @param integer|string $id
     * @param \Dukhanin\Panel\Files\File|null $file
     */
    public function setLoaded($id, File $file = null)
    {
        $this->loadedFiles[$id] = $file;
    }

    /**
     * @param integer|string $id
     *
     * @return bool
     */
    public function isLoaded($id)
    {
        return array_key_exists($id, $this->loadedFiles);
    }

    /**
     * @param integer|string $id
     *
     * @return null|\Dukhanin\Panel\Files\File
     */
    public function getLoaded($id)
    {
        return is_null($id) ? null : array_get($this->loadedFiles, $id);
    }

    /**
     * @param integer|string|array $id
     */
    public function forget($id)
    {
        array_forget($this->loadedFiles, $id);
    }

    /**
     * @return string
     */
    public function getFileClass()
    {
        return $this->fileClass;
    }

    /**
     * @param string $class
     */
    public function setFileClass(string $class)
    {
        $this->fileClass = $class;
    }
}