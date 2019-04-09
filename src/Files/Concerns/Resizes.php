<?php

namespace Dukhanin\Panel\Files\Concerns;

use Illuminate\Support\Facades\File as Filesystem;
use Intervention\Image\Facades\Image;
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Files\InvalidImageSourceException;
use Dukhanin\Panel\Files\InvalidSizeDefinitionException;
use SVG\SVGImage;
use Throwable;

trait Resizes
{
    public function getResize($options)
    {
        $options = $this->resolveResizeOptions($options);

        try {
            $resize = $this->children->where('key', $options['key'])->first();

            if (empty($resize)) {
                $resize = $this->createResize($options);
            } elseif ($options['force']) {
                $resize->resize($options['size']);
            }
        } catch (Throwable $e) {
            $resize = null;
        }

        if($resize === null) {
            $resize = new static();

            $resize->path = config('files.types.image.fake');
            $resize->width = $options['size']['width'];
            $resize->height = $options['size']['height'];
        }

        return $resize;
    }

    public function hasResize($key)
    {
        $key = is_string($key) ? $key : $this->resolveResizeOptions($key)['key'];

        return ! empty($this->children()->where('key', $key)->first());
    }

    public function createResize($options)
    {
        $options = $this->resolveResizeOptions($options);

        if (! $this->isValidImageSource($this)) {
            throw new InvalidImageSourceException($this->isDefined() ? 'undefined' : $this->getPath());
        }

        $source = $this;

        $resize = new static;
        $resize->key = $options['key'];
        $resize->parent()->associate($this);

        $pathinfo = pathinfo($source->getPath());
        $fullpath = array_get($pathinfo, 'dirname').'/'.array_get($pathinfo,
                'filename').'-'.$options['key'].(($ext = array_get($pathinfo, 'extension')) ? ".{$ext}" : '');

        Filesystem::copy($source->getPath(), $fullpath);

        $resize->setBaseFile($fullpath);
        $resize->resize($options['size']);
        $resize->save();

        if ($this->relationLoaded('children')) {
            $this->children->push($resize);
        }

        return $resize;
    }

    public function resize($size)
    {
        $size = $this->resolveImageSize($size);

        if (! $this->isValidImageSource($this)) {
            throw new InvalidImageSourceException($this->isDefined() ? 'undefined' : $this->getPath());
        }

        if ($this->isSizeActual($size)) {
            return false;
        }

        if ($this->isSvg()) {
            $this->resizeSvg($size);
        } else {
            $this->resizeImage($size);
        }

        $this->updateFileAttributes();

        return true;
    }

    protected function resizeImage($size)
    {
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

        return $image->save(null, config('upload.images.quality', null));
    }

    protected function resizeSvg($size)
    {
        $svg = SVGImage::fromFile($this->getPath());
        $svgDocument = $svg->getDocument();

        $svgDocument->setWidth("{$size['width']}px");
        $svgDocument->setHeight("{$size['height']}px");

        return @file_put_contents($this->getPath(), $svg->toXMLString());
    }

    public function crop($width, $height, $x = null, $y = null)
    {
        if (! $this->isValidImageSource($this)) {
            throw new InvalidImageSourceException($this->isDefined() ? 'undefined' : $this->getPath());
        }

        $area = [
            'w' => intval($width),
            'h' => intval($height),
            'x' => is_null($x) ? null : intval($x),
            'y' => is_null($y) ? null : intval($y),
        ];

        if ($this->isSvg()) {
            $this->cropSvg($area);
        } else {
            $this->cropImage($area);
        }

        $settings = $this->settings;
        array_set($settings, 'crop.area', $area);
        $this->settings = $settings;

        $this->updateFileAttributes();

        return true;
    }

    protected function cropImage($area)
    {
        $image = Image::make($this->getPath());

        $image->crop($area['w'], $area['h'], $area['x'], $area['y']);

        return $image->save();
    }

    protected function cropSvg($area)
    {
        $svg = SVGImage::fromFile($this->getPath());
        $svgDocument = $svg->getDocument();

        $svgDocument->setWidth("{$area['w']}px");
        $svgDocument->setHeight("{$area['h']}px");

        $viewBox[1] = $area['x'] ?? 0;
        $viewBox[2] = $area['y'] ?? 0;
        $viewBox[3] = $area['w'];
        $viewBox[4] = $area['h'];

        $svgDocument->setAttribute('viewBox', implode(' ', $viewBox));
        return @file_put_contents($this->getPath(), $svg->toXMLString());
    }

    public function isSizeActual($size)
    {
        $size = $this->resolveImageSize($size);

        if (is_null($this->getWidth()) || is_null($this->getHeight())) {
            return null;
        }

        if ($size['static']) {
            return $this->getWidth() == $size['width'] && $this->getHeight() == $size['height'];
        }

        $originalK = $this->getWidth() / $this->getHeight();
        $resizeK = $size['width'] / $size['height'];
        $criteria = $originalK <= $resizeK ? 'height' : 'width';

        if ($size['enlarge'] && $size['reduce']) {
            return $this->{'get'.$criteria}() === $size[$criteria];
        }

        if ($size['enlarge']) {
            return $this->{'get'.$criteria}() >= $size[$criteria];
        }

        return $this->{'get'.$criteria}() <= $size[$criteria];
    }

    public static function parseImageSize($size)
    {
        if (! preg_match('/^(\d+)(x{1,2})(\d+)([-\+]{0,2})\s*$/i', strtolower(strval($size)), $p)) {
            return false;
        }

        $parsed = [
            'width' => intval($p[1]),
            'height' => intval($p[3]),
            'static' => strlen($p[2]) == 2,
            'enlarge' => str_contains($p[4], '+'),
            'reduce' => ! str_contains($p[4], '+') || str_contains($p[4], '-'),
        ];

        return $parsed;
    }

    protected function resolveImageSize($size)
    {
        $size = is_array($size) ? $size : static::parseImageSize($size);

        if (empty($size) || empty($size['width']) || empty($size['height'])) {
            throw new InvalidSizeDefinitionException(json_encode(func_get_arg(0)));
        }

        return $size + [
                'static' => null,
                'enlarge' => null,
                'reduce' => null,
            ];
    }

    protected function resolveResizeOptions($options)
    {
        $options = (is_array($options) ? $options : ['key' => $options, 'size' => $options]) + [
                'key' => null,
                'force' => false,
                'size' => null,
            ];

        if (!$options['key']) {
            $options['key'] = $this->sizeToKey($options['size']);
        } elseif ($parsed = static::parseImageSize($options['key'])) {
            $options['key'] = $this->sizeToKey($options['key']);
        }

        $options['size'] = $this->resolveImageSize($options['size']);

        return $options;
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

    protected function isValidImageSource(File $file)
    {
        return $file->isDefined() && $file->isExists() && $file->isImage() &&
            ($file->isSvg() || ($file->getWidth() > 0 && $file->getHeight() > 0));
    }
}