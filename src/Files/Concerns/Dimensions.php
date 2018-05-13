<?php

namespace Dukhanin\Panel\Files\Concerns;

use SVG\SVGImage;

trait Dimensions
{
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
    
    protected function initWidthAndHeight()
    {
        $this->width = null;
        $this->height = null;

        if ($this->isSvg() && is_readable($this->getPath())) {
            $svg = SVGImage::fromFile($this->getPath());
            $svgDocument = $svg->getDocument();

            $this->width = intval($svgDocument->getWidth());
            $this->height = intval($svgDocument->getHeight());
        } elseif ($this->isImage() && ($size = @getimagesize($this->getPath()))) {
            $this->width = $size[0];
            $this->height = $size[1];
        }
    }
}