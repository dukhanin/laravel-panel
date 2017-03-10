<?php

namespace Dukhanin\Panel\Files;

use Dukhanin\Support\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File as Filesystem;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\File as BaseFile;
use Throwable;

class File extends Model
{

    use HasSettings;

    protected $baseFile;

    protected $children;

    protected $parent;

    protected $casts = [
        'settings' => 'array',
    ];


    public function __construct(array $attributes = [])
    {
        $this->attributes = [
            'parent_id' => null,
            'path'      => null,
            'key'       => null,
            'ext'       => null,
            'size'      => null,
            'mime'      => null,
            'width'     => null,
            'height'    => null,
            'settings'  => null
        ];

        parent::__construct($attributes);
    }


    public function getPath()
    {
        if ( ! $this->isDefined()) {
            return false;
        }

        if (starts_with($this->path, '/')) {
            return $this->path;
        }

        return public_path() . '/' . $this->path;
    }


    public function url()
    {
        if ( ! $this->isDefined()) {
            return '#undefined';
        }

        return '/' . ltrim($this->path, '/');
    }


    public function children()
    {
        if (is_null($this->children)) {
            $this->initChildren();
        }

        return $this->children;
    }


    public function getParent()
    {
        if (is_null($this->parent)) {
            $this->initParent();
        }

        return $this->parent === 'self' ? $this : $this->parent;
    }


    public function getWidth()
    {
        if (is_null($this->width)) {
            $this->initWidthAndHeight();
        }

        return $this->width;
    }


    public function getHeight()
    {
        if (is_null($this->height)) {
            $this->initWidthAndHeight();
        }

        return $this->height;
    }


    public function getResize($options)
    {
        // @todo @dukhanin add force+config option
        // @todo @dukhanin add resize using time

        $options = $this->resolveResizeOptions($options);

        if (empty($options['size'])) {
            return null;
        }

        try {
            $resize = $this->children()->where('key', $options['key'])->first();

            if (empty($resize)) {
                $resize = $this->createResize($options);
            } elseif ($options['force']) {
                $resize->resize($options['size']);
            }
        } catch (Throwable $e) {
            if (App::environment('local', 'development')) {
                $file = new File();

                $file->path   = config('files.types.image.fake'); // @todo add this value to config
                $file->width  = $options['size']['width'];
                $file->height = $options['size']['height'];

                return $file;
            }

            throw $e;
        }

        return $resize;
    }


    public function getBaseFile()
    {
        if (is_null($this->baseFile)) {
            $this->initBaseFile();
        }

        return $this->baseFile;
    }


    public function initChildren()
    {
        $this->children = File::parent($this->id)->get();
    }


    public function initParent()
    {
        if (empty($this->parent_id)) {
            $this->parent = 'self';
        }

        $this->parent = File::find($this->parent_id);
    }


    public function initWidthAndHeight()
    {
        $this->attributes['width']  = 0;
        $this->attributes['height'] = 0;

        if ($this->isImage() && ( $size = @getimagesize($this->getPath()) )) {
            $this->attributes['width']  = $size[0];
            $this->attributes['height'] = $size[1];
        }
    }


    public function initBaseFile()
    {
        $this->baseFile = new BaseFile(empty($this->attributes['path']) ? null : $this->attributes['path'], false);
    }


    public function setBaseFile($file)
    {
        if ($file instanceof BaseFile) {
            $this->baseFile = $file;
        } elseif (is_string($file)) {
            $this->baseFile = new BaseFile($file, false);
        } else {
            $this->baseFile = false;
        }

        $this->updateFileAttributes();
    }


    public function isDefined()
    {
        return ! empty($this->path);
    }


    public function isMime($type = null, $format = null)
    {
        if (empty($this->mime)) {
            return false;
        }

        if (is_null($type) && is_null($format)) {
            return false;
        }

        list($fileType, $fileFormat) = explode('/', strval($this->mime));

        $fileType   = mb_strtolower($fileType);
        $fileFormat = mb_strtolower($fileFormat);

        if ( ! is_null($type)) {
            if ( ! is_array($type)) {
                $type = [ $type ];
            }

            $type = array_map('mb_strtolower', $type);

            if (array_search($fileType, $type, true) === false) {
                return false;
            }
        }

        if ( ! is_null($format)) {
            if ( ! is_array($format)) {
                $format = [ $format ];
            }

            $format = array_map('mb_strtolower', $format);

            if (array_search($fileFormat, $format, true) === false) {
                return false;
            }
        }

        return true;
    }


    public function isExtension($extensions = [])
    {
        if ( ! is_array($extensions)) {
            $extensions = [ $extensions ];
        }

        $extensions = array_map('mb_strtolower', $extensions);
        $extension  = mb_strtolower($this->getExtension());

        return in_array($extension, $extensions, true);
    }


    public function isImage()
    {
        $type = config('files.types.image', []);

        return $this->isMime('image', $type['formats']) || $this->isExtension($type['extensions']);
    }


    public function isVideo()
    {
        if ($this->isMime('video')) {
            return true;
        }

        if ($this->isMime([ 'audio', 'image' ])) {
            return false;
        }

        $type = config('files.types.video', []);

        return $this->isMime(null, $type['formats']) || $this->isExtension($type['extensions']);
    }


    public function isAudio()
    {
        if ($this->isMime('audio')) {
            return true;
        }

        if ($this->isMime([ 'video', 'image' ])) {
            return false;
        }

        $type = config('files.types.audio', []);

        return $this->isMime(null, $type['formats']) || $this->isExtension($type['extensions']);
    }


    public function isDocument()
    {
        if ($this->isMime([ 'audio', 'video', 'image' ])) {
            return false;
        }

        $type = config('files.types.document', []);

        return $this->isMime(null, $type['formats']) || $this->isExtension($type['extensions']);
    }


    public function isJustUploaded()
    {
        return $this->getBaseFile() instanceof UploadedFile;
    }


    public function isSizeActual()
    {
        // @todo @dukhanin
        return false;
    }


    public function hasResize($options)
    {
        $options = $this->resolveResizeOptions($options);

        return ! empty($this->children()->where('key', $options['key'])->first());
    }


    public function createResize($options)
    {
        $options = $this->resolveResizeOptions($options);

        $source = $this;

        if ( ! $source->isImage()) {
            return null;
        }

        $info = pathinfo($source->getPath());

        $resize            = new File;
        $resize->parent_id = $this->id;
        $resize->key       = $options['key'];
        $resize->path      = $info['dirname'] . '/' . $info['filename'] . '-' . $options['key'] . ( $info['extension'] ? '.' . $info['extension'] : '' );

        Filesystem::copy($source->getPath(), $resize->getPath()); // @todo @dukhanin dev-mode and error-handling

        $resize->resize($options['size']);
        $resize->save();

        $this->children()->put($options['key'], $resize);

        return $resize;
    }


    public function resize($size)
    {
        $size = $this->resolveImageSize($size);

        if (empty($size)) {
            return;
        }

        if ($this->isSizeActual($size)) {
            return;
        }

        $image = Image::make($this->getPath());

        if ($size['static']) {
            $image->fit($size['width'], $size['height'], null);
        } else {
            $image->resize($size['width'], $size['height'], function ($constraint) use ($size) {
                $constraint->aspectRatio();

                if (empty($size['enlarge'])) {
                    $constraint->upsize();
                }
            });
        }

        $image->save();

        $this->updateFileAttributes();
    }


    public function crop($width, $height, $x = null, $y = null)
    {
        $image = Image::make($this->getPath());

        $area = [
            'w' => intval($width),
            'h' => intval($height),
            'x' => is_null($x) ? null : intval($x),
            'y' => is_null($y) ? null : intval($y)
        ];

        $image->crop($area['w'], $area['h'], $area['x'], $area['y']);

        $image->save();

        $this->settings('crop.area', $area);

        $this->updateFileAttributes();
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
        if ( ! $this->isDefined()) {
            return '';
        }

        return html_tag('img', $this->getHtmlTagAttributes(), is_array($attributes) ? $attributes : []);
    }


    public function attr($attributes = null)
    {
        $attributes = array_merge_recursive($this->getHtmlTagAttributes(), is_array($attributes) ? $attributes : []);

        return html_tag_attr($attributes);
    }


    public function jsonSerialize()
    {
        $serialized = parent::jsonSerialize();

        return [
                'url'         => $this->url(),
                'is_image'    => $this->isImage(),
                'is_video'    => $this->isVideo(),
                'is_audio'    => $this->isAudio(),
                'is_document' => $this->isDocument(),
                'children'    => $this->children()->map(function ($file) {
                    return $file->jsonSerialize();
                })
            ] + $serialized;
    }


    public function scopeParent($query, $parentId)
    {
        return $query->where('parent_id', '=', intval($parentId));
    }


    public function __call($method, $arguments)
    {
        if (method_exists($this->getBaseFile(), $method)) {
            return $this->getBaseFile()->$method(...$arguments);
        }

        return parent::__call($method, $arguments);
    }


    public static function parseImageSize($size)
    {
        $size = strval($size);
        $size = strtolower($size);

        if ( ! preg_match('/^(\d+)(x{1,2})(\d+)([-\+]{0,2})\s*$/i', $size, $p)) {
            return null;
        }

        $parsed = [
            'width'   => intval($p[1]),
            'height'  => intval($p[3]),
            'static'  => strlen($p[2]) == 2,
            'enlarge' => false,
            'reduce'  => true
        ];

        if (str_contains($p[4], '+')) {
            $parsed['enlarge'] = true;
        }

        if (str_contains($p[4], '-')) {
            $parsed['reduce'] = true;
        }

        return $parsed;
    }


    public function newEloquentBuilder($query)
    {
        return new FilesQueryBuilder($query);
    }


    protected function resolveImageSize($size)
    {
        if ( ! is_array($size)) {
            $size = static::parseImageSize($size);
        }

        if (empty($size)) {
            return null;
        }

        return array_merge([
            'width'   => null,
            'height'  => null,
            'static'  => null,
            'enlarge' => null,
            'reduce'  => null
        ], $size);
    }


    protected function resolveResizeOptions($options)
    {
        if ( ! is_array($options)) {
            $options = [
                'key'  => $this->sizeToKey($options),
                'size' => $options
            ];
        }

        $options = $options + [
                'key'   => null,
                'force' => false,
                'size'  => ''
            ];

        $options['size'] = $this->resolveImageSize($options['size']);

        return $options;
    }


    protected function sanitizePath($path)
    {
        $path = strval($path);

        if (starts_with($path, storage_path('app/public'))) {
            return preg_replace('#^' . preg_quote(storage_path('app/public')) . '/*#', 'upload/', $path);
        }

        if (starts_with($path, public_path())) {
            return preg_replace('#^' . preg_quote(public_path()) . '/*#', '', $path);
        }

        return $path;
    }


    protected function sizeToKey($size)
    {
        $size = $this->resolveImageSize($size);

        return implode([
            $size['width'],
            $size['static'] ? 'xx' : 'x',
            $size['height']
        ]);
    }


    protected function getHtmlTagAttributes()
    {
        if ($this->isImage()) {
            return [
                'src'    => $this->url(),
                'width'  => $this->getWidth() ? $this->getWidth() : null,
                'height' => $this->getHeight() ? $this->getHeight() : null,
            ];
        }
    }


    protected function updateFileAttributes()
    {
        if ( ! ( $baseFile = $this->getBaseFile() )) {
            return $this->clearFileAttributes();
        }

        $this->attributes['path'] = $this->sanitizePath($baseFile->getPathname());
        $this->attributes['ext']  = $baseFile->getExtension();

        if (Filesystem::exists($baseFile->getPath())) {
            $this->attributes['size'] = $baseFile->getSize();
            $this->attributes['mime'] = $baseFile->getMimeType();
        }

        if ($this->isJustUploaded()) {
            $this->settings('upload_info', [
                'extension' => $baseFile->clientExtension(),
                'name'      => $baseFile->getClientOriginalName(),
                'type'      => $baseFile->getClientMimeType(),
                'size'      => $baseFile->getClientSize(),
                'error'     => $baseFile->getError()
            ]);
        }

        if ($this->isImage()) {
            $this->initWidthAndHeight();
        }
    }


    protected function clearFileAttributes()
    {
        $this->attributes['path'] = null;
        $this->attributes['ext']  = null;
        $this->attributes['size'] = null;
        $this->attributes['mime'] = null;
    }
}
