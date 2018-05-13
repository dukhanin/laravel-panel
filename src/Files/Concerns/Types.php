<?php

namespace Dukhanin\Panel\Files\Concerns;

trait Types
{
    public function isMime($formats)
    {
        if (empty($this->mime)) {
            return false;
        }

        $formats = collect((array)$formats)->map(function ($option) {
            return $this->normalizeMime($option);
        });

        list($type, $format) = explode('/', $mime = $this->normalizeMime($this->mime));

        return $formats->intersect([$mime, $type, $format])->isNotEmpty();
    }

    public function isExtension($extensions)
    {
        $extension = $this->normalizeExtension($this->getExtension());

        $extensions = collect(is_Array($extensions) ? $extensions : [$extensions])->map(function ($extension) {
            return $this->normalizeExtension($extension);
        });

        return $extensions->contains($extension);
    }

    public function isImage()
    {
        $type = config('files.types.image', []);

        return $this->isMime($type['formats']) || $this->isExtension($type['extensions']);
    }

    public function isSvg()
    {
        return $this->isMime('image/svg+xml');
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

        return $this->isMime($type['formats']) || $this->isExtension($type['extensions']);
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

        return $this->isMime($type['formats']) || $this->isExtension($type['extensions']);
    }

    public function isDocument()
    {
        if ($this->isMime(['audio', 'video', 'image'])) {
            return false;
        }

        $type = config('files.types.document', []);

        return $this->isMime($type['formats']) || $this->isExtension($type['extensions']);
    }

    protected function normalizeMime($string)
    {
        return mb_strtolower(strval($string));
    }

    protected function normalizeExtension($string)
    {
        return mb_strtolower(strval($string));
    }
}