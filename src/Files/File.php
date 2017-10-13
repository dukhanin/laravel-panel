<?php

namespace Dukhanin\Panel\Files;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as Filesystem;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\File as BaseFile;
use Throwable;

class File extends Model
{
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

        if (starts_with($this->path, '/')) {
            return $this->path;
        }

        return public_path().'/'.$this->path;
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
        $options = $this->resolveResizeOptions($options);

        if (empty($options['size'])) {
            return null;
        }

        try {
            $resize = $this->children->where('key', $options['key'])->first();

            if (empty($resize) || $options['force']) {
                $resize = $this->createResize($options);
            } elseif ($options['force']) {
                $resize->resize($options['size']);
            }
        } catch (Throwable $e) {
            // @todo сделать человечью обработку ошибок
            $file = new static();

            $file->path = config('files.types.image.fake');
            $file->width = $options['size']['width'];
            $file->height = $options['size']['height'];

            return $file;
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

    public function initWidthAndHeight()
    {
        $this->attributes['width'] = null;
        $this->attributes['height'] = null;

        if ($this->isImage() && ($size = @getimagesize($this->getPath()))) {
            $this->attributes['width'] = $size[0];
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
            $this->baseFile = new BaseFile($file, '');
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

    public function isMime($type = null, $format = null)
    {
        if (empty($this->mime)) {
            return false;
        }

        if (is_null($type) && is_null($format)) {
            return false;
        }

        list($fileType, $fileFormat) = explode('/', strval($this->mime));

        $fileType = mb_strtolower($fileType);
        $fileFormat = mb_strtolower($fileFormat);

        if (! is_null($type)) {
            if (! is_array($type)) {
                $type = [$type];
            }

            $type = array_map('mb_strtolower', $type);

            if (array_search($fileType, $type, true) === false) {
                return false;
            }
        }

        if (! is_null($format)) {
            if (! is_array($format)) {
                $format = [$format];
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
        if (! is_array($extensions)) {
            $extensions = [$extensions];
        }

        $extensions = array_map('mb_strtolower', $extensions);
        $extension = mb_strtolower($this->getExtension());

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

        if ($this->isMime(['audio', 'image'])) {
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

        if ($this->isMime(['video', 'image'])) {
            return false;
        }

        $type = config('files.types.audio', []);

        return $this->isMime(null, $type['formats']) || $this->isExtension($type['extensions']);
    }

    public function isDocument()
    {
        if ($this->isMime(['audio', 'video', 'image'])) {
            return false;
        }

        $type = config('files.types.document', []);

        return $this->isMime(null, $type['formats']) || $this->isExtension($type['extensions']);
    }

    public function isJustUploaded()
    {
        return $this->getBaseFile() instanceof UploadedFile;
    }

    public function isSizeActual($size)
    {
        if (empty($size = $this->resolveImageSize($size)) || is_null($this->getWidth()) || is_null($this->getHeight())) {
            return false;
        }

        if ($this->getWidth() == $size['width'] && $this->getHeight() == $size['height']) {
            return true;
        }

        if ($size['static']) {
            return false;
        }

        if (! $size['enlarge']) {
            return $this->getWidth() <= $size['width'] && $this->getHeight() <= $size['height'];
        }

        return $size['width'] / $size['height'] > 1 ? $this->getWidth() == $size['width'] : $this->getHeight() == $size['height'];
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

        if (! $source->isImage()) {
            return null;
        }

        $info = pathinfo($source->getPath());

        $resize = new static;
        $resize->parent()->associate($this);
        $resize->key = $options['key'];;

        Filesystem::copy($source->getPath(), $fullPath = $info['dirname'].'/'.$info['filename'].'-'.$options['key'].($info['extension'] ? '.'.$info['extension'] : ''));

        $resize->setBaseFile($fullPath);
        $resize->resize($options['size']);
        $resize->save();

        if ($this->relationLoaded('children')) {
            $this->children->push($resize);
        }

        return $resize;
    }

    public function resize($size)
    {
        if (! $this->isDefined() || empty($size = $this->resolveImageSize($size))) {
            return false;
        }

        if ($this->isSizeActual($size)) {
            return true;
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

        $image->save(null, config('upload.images.quality', null));

        $this->updateFileAttributes();

        return true;
    }

    public function crop($width, $height, $x = null, $y = null)
    {
        if (! $this->isDefined()) {
            return false;
        }

        $image = Image::make($this->getPath());

        $area = [
            'w' => intval($width),
            'h' => intval($height),
            'x' => is_null($x) ? null : intval($x),
            'y' => is_null($y) ? null : intval($y),
        ];

        $image->crop($area['w'], $area['h'], $area['x'], $area['y']);

        $image->save();

        $settings = $this->settings;
        array_set($settings, 'crop.area', $area);
        $this->settings = $settings;

        $this->updateFileAttributes();

        return true;
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
        if (! $this->isDefined()) {
            return '';
        }

        return html_tag('img', $this->htmlTagAttributes(), is_array($attributes) ? $attributes : []);
    }

    public function attr(array $attributes = [])
    {
        $attributes = array_merge_recursive($this->htmlTagAttributes(), $attributes);

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
                }),
            ] + parent::jsonSerialize();
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

        if (! preg_match('/^(\d+)(x{1,2})(\d+)([-\+]{0,2})\s*$/i', $size, $p)) {
            return null;
        }

        $parsed = [
            'width' => intval($p[1]),
            'height' => intval($p[3]),
            'static' => strlen($p[2]) == 2,
            'enlarge' => false,
            'reduce' => true,
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
        if (! is_array($size)) {
            $size = static::parseImageSize($size);
        }

        if (empty($size)) {
            return null;
        }

        return array_merge([
            'width' => null,
            'height' => null,
            'static' => null,
            'enlarge' => null,
            'reduce' => null,
        ], $size);
    }

    protected function resolveResizeOptions($options)
    {
        if (! is_array($options)) {
            $options = [
                'key' => $options,
                'size' => $options,
            ];
        }

        $options = $options + [
                'key' => null,
                'force' => false,
                'size' => '',
            ];

        $options['key'] = $options['key'] ?: $this->sizeToKey($options['size']);
        $options['size'] = $this->resolveImageSize($options['size']);

        return $options;
    }

    protected function sanitizePath($path)
    {
        $path = strval($path);

        if (starts_with($path, config('upload.path'))) {
            return preg_replace('#^'.preg_quote(config('upload.path')).'/*#', trim(config('upload.url'), '/').'/', $path);
        }

        if (starts_with($path, public_path())) {
            return preg_replace('#^'.preg_quote(public_path()).'/*#', '', $path);
        }

        return $path;
    }

    protected function sizeToKey($size)
    {
        $size = $this->resolveImageSize($size);

        return implode([
            $size['width'],
            $size['static'] ? 'xx' : 'x',
            $size['height'],
        ]);
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
        $this->attributes['path'] = $this->sanitizePath($this->getBaseFile()->getPathname());
        $this->attributes['ext'] = $this->getBaseFile()->getExtension();

        if ($this->isExists()) {
            $this->attributes['size'] = $this->getBaseFile()->getSize();
            $this->attributes['mime'] = $this->getBaseFile()->getMimeType();
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
}
