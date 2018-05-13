<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\File as Filesystem;
use Symfony\Component\HttpFoundation\File\File as BaseFile;

$createFile = function ($wildcard = null, $data = []) {
    $width = array_get($data, 'width');
    $height = array_get($data, 'height');
    $subdir = isset($data['subdir']) ? '/'.trim($data['subdir'], '/') : null;
    $subdir = $subdir ?? (isset($data['faker']) ? '/_faker' : null) ?? '';
    $uploadPath = rtrim(config('upload.path'), '/').$subdir;
    $uploadUrl = rtrim(config('upload.url'), '/').$subdir;

    if (! Filesystem::isDirectory($uploadPath)) {
        Filesystem::makeDirectory($uploadPath, 0777, true);
    }

    if ($faker = array_get($data, 'faker')) {
        $filepath = $faker->image($uploadPath, $width ?? 640, $height ?? 480);
    } else {
        $wildcard = realpath(__DIR__.'/../../tests/mocks').'/'.$wildcard;
        $wildcard .= ($width || $height) ? '-'.($width ?? '*').'x'.($height ?? '*') : '';
        $wildcard .= '*'.(isset($data['ext']) ? ".{$data['ext']}" : '');

        if (($entries = collect(Filesystem::glob($wildcard)))->isEmpty()) {
            throw new \Exception("No file found using wildcard {$wildcard}");
        }

        $ext = pathinfo($source = $entries->random(), PATHINFO_EXTENSION);
        $filepath = $uploadPath.'/'.str_random().($ext ? '.'.$ext : '');
        Filesystem::copy($source, $filepath);
    }

    $baseFile = new BaseFile($filepath);

    return array_except($data, ['subdir', 'faker']) + [
            'path' => ltrim(str_replace($uploadPath, $uploadUrl, $filepath), '/'),
            'ext' => $baseFile->getExtension(),
            'size' => $baseFile->getSize(),
            'mime' => $baseFile->getMimeType(),
        ];
};

$factory->define(\Dukhanin\Panel\Files\File::class, function (Faker $faker) use ($createFile) {
    return [];
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'defined', function (Faker $faker, $data) use ($createFile) {
    return [
        'path' => trim(config('upload.url'), '/').'/'.str_random().'.'.($ext = $faker->fileExtension),
        'mime' => $faker->mimeType,
        'ext' => $ext,
        'size' => mt_rand(100, 30000),
    ];
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'exists', function (Faker $faker, $data) use ($createFile) {
    return $createFile('*', $data);
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'image', function (Faker $faker, $data) use ($createFile) {
    return $createFile('image', $data);
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'document', function (Faker $faker, $data) use ($createFile) {
    return $createFile('document', $data);
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'video', function (Faker $faker, $data) use ($createFile) {
    return $createFile('video', $data);
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'audio', function (Faker $faker, $data) use ($createFile) {
    return $createFile('audio', $data);
});

$factory->state(\Dukhanin\Panel\Files\File::class, 'faker-image', function (Faker $faker, $data) use ($createFile) {
    return $createFile('audio', $data + ['faker' => $faker]);
});