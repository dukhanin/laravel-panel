<?php

namespace Dukhanin\Panel\Files;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as Filesystem;
use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends Model
{
    use Concerns\Types, Concerns\Dimensions, Concerns\Resizes;

    protected $baseFile;

    protected $casts = [
        'settings' => 'array',
    ];

    protected $hidden = [
        'parent',
    ];

    public function __construct(array $attributes = [])
    {
        $this->attributes = [
            'parent_id' => null,
            'path' => null,
            'key' => null,
            'ext' => null,
            'size' => null,
            'mime' => null,
            'width' => null,
            'height' => null,
            'settings' => null,
        ];

        parent::__construct($attributes);
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function getPath()
    {
        if (! $this->isDefined()) {
            return null;
        }

        return $this->pathToAbsolute($this->path);
    }

    public function getUrl()
    {
        if (! $this->isDefined()) {
            return '#undefined';
        }

        return '/'.ltrim($this->path, '/');
    }

    /**
     * @deprecated
     * @return string
     */
    public function url()
    {
        return $this->getUrl();
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }

    public function getBaseFile()
    {
        if (is_null($this->baseFile)) {
            $this->initBaseFile();
        }

        return $this->baseFile;
    }

    public function initBaseFile()
    {
        // @refactor
        $this->baseFile = new BaseFile($this->pathToAbsolute($this->path) ?? '', false);
    }

    public function setBaseFile($file)
    {
        if ($file instanceof BaseFile) {
            $this->baseFile = $file;
        } elseif (is_string($file)) {
            $this->baseFile = new BaseFile($this->pathToAbsolute($file) ?? '', false);
        }

        $this->updateFileAttributes();
    }

    public function isDefined()
    {
        return ! empty($this->path);
    }

    public function isExists()
    {
        return Filesystem::exists($this->getPath());
    }

    public function isJustUploaded()
    {
        return $this->getBaseFile() instanceof UploadedFile;
    }

    public function delete()
    {
        $this->children()->each(function ($file) {
            $file->delete();
        });

        $this->remove();

        return parent::delete();
    }

    public function copy($filepath)
    {
        $file = new static;

        Filesystem::copy($this->getPath(), $filepath);

        $file->setBaseFile($filepath);

        return $file;
    }

    public function move($newDistanation)
    {
        if (Filesystem::move($this->getPath(), $newDistanation)) {
            $this->setBaseFile($newDistanation);
        }
    }

    public function remove()
    {
        Filesystem::delete($this->getPath());
        $this->setBaseFile(false);
    }

    public function img($attributes = null)
    {
        if (! $this->isImage()) {
            return '';
        }

        return html_tag('img', $this->htmlTagAttributes(), is_array($attributes) ? $attributes : []);
    }

    public function attr(array $attributes = [])
    {
        $attributes = $attributes + $this->htmlTagAttributes();

        return html_tag_attr($attributes);
    }

    public function jsonSerialize()
    {
        return [
                'url' => $this->getUrl(),
                'is_image' => $this->isImage(),
                'is_video' => $this->isVideo(),
                'is_audio' => $this->isAudio(),
                'is_document' => $this->isDocument(),
                'children' => $this->children->map(function ($file) {
                    return $file->jsonSerialize();
                })->toArray(),
            ] + parent::jsonSerialize();
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->getBaseFile(), $method)) {
            return $this->getBaseFile()->$method(...$arguments);
        }

        return parent::__call($method, $arguments);
    }

    function __sleep()
    {
        return array_keys(array_except(get_object_vars($this), ['baseFile']));
    }

    protected function htmlTagAttributes()
    {
        if ($this->isImage()) {
            return [
                'src' => $this->getUrl(),
                'width' => $this->width ?: null,
                'height' => $this->height ?: null,
            ];
        }

        return [];
    }

    protected function updateFileAttributes()
    {
        $this->path = $this->pathToRelative($this->getBaseFile()->getPathname());
        $this->ext = $this->getBaseFile()->getExtension();

        if ($this->isExists()) {
            clearstatcache();
            $this->size = $this->getBaseFile()->getSize();
            $this->mime = $this->getBaseFile()->getMimeType();
        }

        if ($this->isJustUploaded()) {
            $settings = $this->settings;

            $this->settings = array_set($settings, 'upload_info', [
                'extension' => $this->getBaseFile()->clientExtension(),
                'name' => $this->getBaseFile()->getClientOriginalName(),
                'type' => $this->getBaseFile()->getClientMimeType(),
                'size' => $this->getBaseFile()->getClientSize(),
                'error' => $this->getBaseFile()->getError(),
            ]);
        }

        if ($this->isImage()) {
            $this->initWidthAndHeight();
        }
    }

    protected function pathToAbsolute($path)
    {
        if (starts_with($path, '/')) {
            return $path;
        }

        if (starts_with($path, $dir = trim(config('upload.url'), '/'))) {
            return preg_replace('#^'.preg_quote($dir).'/*#', config('upload.path').'/', $path);
        }

        return is_null($path) ? null : public_path($path);
    }

    protected function pathToRelative($path)
    {
        if (starts_with($path, config('upload.path'))) {
            return preg_replace('#^('.preg_quote(config('upload.path')).')/*#', trim(config('upload.url'), '/').'/',
                $path);
        }

        if (starts_with($path, public_path())) {
            return preg_replace('#^'.preg_quote(public_path()).'/*#', '', $path);
        }

        return $path;
    }
}
