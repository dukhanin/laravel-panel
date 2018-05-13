<?php

namespace Dukhanin\Panel\Tests\Unit\Files;

use Faker\Factory as FakerFactory;
use Dukhanin\Panel\Tests\TestCase;
use Dukhanin\Panel\Files\File;

class TypesTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        $this->createApplication();

        $this->faker = FakerFactory::create();
    }

    public function test_isMime_returns_false_if_mime_is_undefined()
    {
        $file = factory(File::class)->make();

        $this->assertFalse($file->isMime('image/jpeg'));
    }

    public function test_isMime_checks_formats()
    {
        $file = factory(File::class)->make([
            'mime' => 'image/jpeg',
        ]);

        $this->assertTrue($file->isMime('image'));
        $this->assertTrue($file->isMime(['image', 'audio']));
        $this->assertFalse($file->isMime('video'));
        $this->assertFalse($file->isMime(['video', 'audio']));
    }

    public function test_isMime_checks_types()
    {
        $file = factory(File::class)->make([
            'mime' => 'image/jpeg',
        ]);

        $this->assertTrue($file->isMime('image/jpeg'));
        $this->assertTrue($file->isMime(['image/jpeg', 'audio/mp3']));
        $this->assertFalse($file->isMime('video/mp4'));
        $this->assertFalse($file->isMime(['video/mp4', 'audio/mp3']));
    }

    public function test_isExtension_returns_false_if_file_is_undefined()
    {
        $file = factory(File::class)->make();

        $this->assertFalse($file->isExtension('jpg'));
    }

    public function test_isExtension_on_empty_extension()
    {
        $file = factory(File::class)->make(['path' => 'storage/hello', 'ext' => null]);
        $this->assertTrue($file->isExtension(null));
        $this->assertTrue($file->isExtension(''));

        $file = factory(File::class)->make(['path' => 'storage/hello', 'ext' => '']);
        $this->assertTrue($file->isExtension(null));
        $this->assertTrue($file->isExtension(''));

        $file = factory(File::class)->states('defined')->make();
        $this->assertFalse($file->isExtension(null));
        $this->assertFalse($file->isExtension(''));
    }

    public function test_isExtension()
    {
        $file = factory(File::class)->make(['path' => 'storage/image.jpg']);
        $this->assertTrue($file->isExtension('jpg'));
        $this->assertTrue($file->isExtension('JPG'));
        $this->assertTrue($file->isExtension(['jpg', 'png']));
        $this->assertTrue($file->isExtension(['JPG', 'PNG']));

        $this->assertFalse($file->isExtension('png'));
        $this->assertFalse($file->isExtension(['png', 'bmp', null]));
        $this->assertFalse($file->isExtension('PNG'));
        $this->assertFalse($file->isExtension(['PNG', 'BMP', null]));
    }

    public function test_isDocument()
    {
        $this->checkIsTypeMethod('document');
    }

    public function test_isImage()
    {
        $this->checkIsTypeMethod('image');
    }

    public function test_isAudio()
    {
        $this->checkIsTypeMethod('audio');
    }

    public function test_isVideo()
    {
        $this->checkIsTypeMethod('video');
    }

    protected function checkIsTypeMethod($type)
    {
        $this->assertTrue(factory(File::class)->make(['mime' => 'application/msword'])->isDocument());

        foreach ($this->differentFiles() as $mockType => $files) {
            foreach ($files as $file) {
                $filename = pathinfo($file->getUrl(), PATHINFO_BASENAME);

                $assert = $mockType === $type ? 'assertTrue' : 'assertFalse';
                $isMethod = 'is'.ucfirst($type);

                $not = $mockType === $type ? 'not' : '';
                $message = "File::{$isMethod}(): File([filename: {$filename}, mime: {$file->mime}]) is {$not} identified like a/an {$mockType};";

                $this->$assert($file->$isMethod(), $message);
            }
        }
    }

    protected function differentFiles()
    {
        return [
            'document' => [
                factory(File::class)->states('document')->make(),
                factory(File::class)->make(['mime' => 'application/msword']),
            ],
            'image' => [
                factory(File::class)->states('image')->make(),
                factory(File::class)->make(['mime' => 'image/jpeg']),
            ],
            'audio' => [
                factory(File::class)->states('audio')->make(),
                factory(File::class)->make(['mime' => 'audio/x-realaudio']),
            ],
            'video' => [
                factory(File::class)->states('video')->make(),
                factory(File::class)->make(['mime' => 'video/mpeg']),
            ],
            'undefined' => [
                factory(File::class)->make(),
            ],
        ];
    }
    /*
        public function testDelete()
        {
            $parent = new File;
            $parent->setBaseFile($parentPath = $this->mocks('test-400x300.jpg'));
            $parent->save();

            $child = new File;
            $child->setBaseFile($childPath = $this->mocks('test-400x400.jpg'));
            $child->parent()->associate($parent);
            $child->save();

            $this->assertFileExists($parentPath);
            $this->assertFileExists($childPath);
            $this->assertEquals(File::findMany($ids = [$parent->id, $child->id])->count(), 2);

            $parent->delete();
            $this->assertFileNotExists($parentPath);
            $this->assertFileNotExists($childPath);
            $this->assertEquals(File::findMany($ids)->count(), 0);
        }

        public function testRemove()
        {
            $file = new File;
            $file->setBaseFile($this->mocks('test-400x300.jpg'));
            $this->assertFileExists($filePath = $file->getPath());

            $file->remove();
            $this->assertFileNotExists($filePath);
        }

        public function testCopy()
        {
            $file = new File;
            $file->setBaseFile($this->mocks('test-400x300.jpg'));

            $this->assertFileNotExists($targetPath = $this->mocks('test-400x300-new.jpg'));

            $copy = $file->copy($targetPath);
            $this->assertFileExists($targetPath);
        }

        public function testMove()
        {

            $file = new File;
            $file->setBaseFile($sourcePath = $this->mocks('test-400x300.jpg'));

            $this->assertFileExists($sourcePath);
            $this->assertFileNotExists($targetPath = $this->mocks('test-400x300-new.jpg'));

            $file->move($targetPath);

            $this->assertFileNotExists($sourcePath);
            $this->assertFileExists($targetPath);
        }

        public function testImg()
        {
            $file = new File;
            $this->assertEmpty($file->img());

            $file->setBaseFile(public_path('image.jpg'));
            $this->assertStringStartsWith('<img ', $file->img());
            $this->assertTrue(str_contains($file->img(), ['src="/image.jpg"', "src='/image.jpg'",]));
            $this->assertTrue(str_contains($file->img(['some-attr' => 'some-value']), [
                'some-attr="some-value"',
                "some-attr='some-value'",
            ]));
        }

        public function testAttr()
        {
            $file = new File;
            $this->assertEmpty($file->attr());

            $file->setBaseFile(public_path('image.jpg'));

            $this->assertTrue(str_contains($file->attr(), ['src="/image.jpg"', "src='/image.jpg'"]));
            $this->assertTrue(str_contains($file->attr(['some-attr' => 'some-value']), [
                'some-attr="some-value"',
                "some-attr='some-value'",
            ]));
        }

        public function testUpdateFileAttributes()
        {
            $file = new File;
            $file->resize('100x100');
        }

        public function testUploadedSettings()
        {
            $uploadedFile = new UploadedFile($this->mocks('test-400x300.jpg'), 'original.jpg', null,
                $filesize = filesize($this->mocks('test-400x300.jpg')), UPLOAD_ERR_OK);

            $file = new File;
            $file->setBaseFile($uploadedFile);

            $this->assertEquals(array_get($file->settings, 'upload_info'), [
                'extension' => $uploadedFile->clientExtension(),
                'name' => $uploadedFile->getClientOriginalName(),
                'type' => $uploadedFile->getClientMimeType(),
                'size' => $uploadedFile->getClientSize(),
                'error' => $uploadedFile->getError(),
            ]);
        }

        public function testJsonSerialize()
        {
            $parent = new File;
            $parent->setBaseFile($this->mocks('test-400x300.jpg'));
            $parent->save();

            $child = new File;
            $child->setBaseFile($this->mocks('test-400x400.jpg'));
            $child->parent()->associate($parent);
            $child->save();

            $json = $parent->jsonSerialize();

            $this->assertEquals(array_get($json, 'url'), $parent->getUrl());
            $this->assertEquals(array_get($json, 'id'), $parent->getKey());

            $this->assertEquals(array_get($json, 'children.0.url'), $child->getUrl());
            $this->assertEquals(array_get($json, 'children.0.id'), $child->getKey());
        }*/
}