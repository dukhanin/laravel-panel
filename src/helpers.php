<?php

if (! function_exists('upload')) {
    function upload()
    {
        return app('upload');
    }
}

if (! function_exists('datepicker_format')) {
    function datepicker_format($format)
    {
        static $assoc = [
            'Y' => 'yyyy',
            'y' => 'yy',
            'F' => 'MM',
            'm' => 'mm',
            'l' => 'DD',
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'M' => 'M',
            'n' => 'm',
            'z' => 'o',
            'N' => '',
            'S' => '',
            'w' => '',
            'W' => '',
            't' => '',
            'L' => '',
            'o' => '',
            'a' => '',
            'A' => '',
            'B' => '',
            'g' => '',
            'G' => '',
            'h' => '',
            'H' => '',
            'i' => '',
            's' => '',
            'u' => '',
        ];

        $keys = array_keys($assoc);

        $indeces = array_map(function ($index) {
            return '{{'.$index.'}}';
        }, array_keys($keys));

        $format = str_replace($keys, $indeces, $format);

        return str_replace($indeces, $assoc, $format);
    }
}