<?php

namespace Dukhanin\Panel\Tests\Unit\Files;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as Filesystem;
use Dukhanin\Panel\Tests\TestCase;
use Dukhanin\Panel\Files\File;
use Symfony\Component\HttpFoundation\File\File as BaseFile;
use SplFileInfo;

class FileTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        Filesystem::copyDirectory(__DIR__.'/../../mocks/', $this->mocks());
    }

    public function tearDown()
    {
        Filesystem::deleteDirectory($this->mocks());

        parent::tearDown();
    }

    public function testConfig()
    {
        $this->assertNotEmpty(config('files.types.image'));
    }

    public function testGetPath()
    {
        $file = new File();
        $this->assertNull($file->getPath());

        $file->path = 'storage/folder/path.ext';
        $this->assertEquals($file->getPath(), public_path('storage/folder/path.ext'));

        $file->path = $fullPath = base_path('storage/folder/path.ext');
        $this->assertEquals($file->getPath(), $fullPath);
    }

    public function testGetUrl()
    {
        $file = new File();
        $this->assertEquals($file->getUrl(), '#undefined');
        $this->assertEquals($file->url, '#undefined');

        $file->setBaseFile(public_path('storage/folder/path.ext'));
        $this->assertEquals($file->getUrl(), '/storage/folder/path.ext');
        $this->assertEquals($file->url, '/storage/folder/path.ext');

        $file->setBaseFile('storage/folder/path.ext');
        $this->assertEquals($file->getUrl(), '/storage/folder/path.ext');
        $this->assertEquals($file->url, '/storage/folder/path.ext');
    }

    public function testChildren()
    {
        $file = new File;
        $file->save();
        $this->assertInstanceOf(HasMany::class, $file->children());
        $this->assertInstanceOf(Collection::class, $file->children);

        $child = new File;
        $child->parent()->associate($file);
        $child->save();

        $file->load('children');
        $this->assertEquals($file->children->count(), 1);
        $this->assertEquals($file->children->first()->id, $child->id);
    }

    public function testParent()
    {
        $file = new File;
        $this->assertInstanceOf(BelongsTo::class, $file->parent());
        $this->assertNull($file->parent);

        $parent = new File;
        $parent->save();

        $file->parent()->associate($parent);

        $this->assertInstanceOf(File::class, $file->parent);
        $this->assertEquals($parent->id, $file->parent->id);
    }

    public function testGetWidth()
    {
        $file = new File();
        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertEquals($file->getWidth(), 400);

        $file = new File();
        $file->setBaseFile($this->mocks('test.txt'));
        $this->assertNull($file->getWidth());

        $file = new File();
        $file->setBaseFile('not_exists');
        $this->assertNull($file->getWidth());
    }

    public function testGetHeight()
    {
        $file = new File();
        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertEquals($file->getHeight(), 300);

        $file = new File();
        $file->setBaseFile($this->mocks('test.txt'));
        $this->assertNull($file->getHeight());

        $file = new File();
        $file->setBaseFile('not_exists');
        $this->assertNull($file->getHeight());
    }

    public function testGetResizeWithSizeOption()
    {
        $this->assertNull((new File)->getResize(['key' => 'small', 'no_size_defined']));
    }

    public function testGetResizeFromUndefined()
    {
        $this->assertNull((new File)->getResize(['key' => 'small', 'size' => '100x100']));
    }

    public function testGetResizeLoaded()
    {
        $file = new File;
        $file->save();

        $resize = new File;
        $resize->key = 'small';
        $resize->parent()->associate($file);
        $resize->save();

        $this->assertEquals($file->getResize(['key' => 'small', 'size' => '100x100'])->id, $resize->id);
    }

    public function testGetResizeCreating()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $resize = $file->getResize(['key' => 'small', 'size' => '100x100']);
        $this->assertEquals($resize->key, 'small');
        $this->assertEquals($resize->getWidth(), 100);
        $this->assertEquals($resize->getHeight(), 75);

        $resize = $file->getResize('100x100');
        $this->assertEquals($resize->key, '100x100');
        $this->assertEquals($resize->getWidth(), 100);
        $this->assertEquals($resize->getHeight(), 75);
    }

    public function testGetResizeWithShortNotation()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $resize = $file->getResize('100x100');
        $this->assertEquals($resize->key, '100x100');
        $this->assertEquals($resize->getWidth(), 100);
        $this->assertEquals($resize->getHeight(), 75);
    }

    public function testGetResizeForced()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $file->getResize(['key' => 'small', 'size' => '200x200']);
        $resize = $file->getResize(['key' => 'small', 'size' => '100x100', 'force' => true]);
        $resize->initWidthAndHeight();
        $this->assertEquals($resize->key, 'small');
        $this->assertEquals($resize->getWidth(), 100);
        $this->assertEquals($resize->getHeight(), 75);
    }

    public function testGetResizeFromInvalidFile()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test.txt'));

        $file->mime = 'image/jpeg';

        $resize = $file->getResize(['key' => 'small', 'size' => '200x200', 'force' => true]);
        $this->assertEquals($resize->getFilename(), pathinfo(config('files.types.image.fake'), PATHINFO_BASENAME));
        $this->assertEquals($resize->getWidth(), 200);
        $this->assertEquals($resize->getHeight(), 200);
    }

    public function testHasResize()
    {
        $file = new File;
        $this->assertFalse($file->hasResize('small'));

        $file->save();
        $this->assertFalse($file->hasResize('small'));

        $resize = new File;
        $resize->key = 'small';
        $resize->parent()->associate($file);
        $resize->save();
        $this->assertTrue($file->hasResize('small'));
    }

    public function testGetBaseFile()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertInstanceOf(BaseFile::class, $file->getBaseFile());
        $this->assertEquals($file->getFilename(), 'test-400x300.jpg');
    }

    public function testInitWidthAndHeight()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->width, 400);
        $this->assertEquals($file->height, 300);

        $file->setBaseFile($this->mocks('test.txt'));
        $file->initWidthAndHeight();
        $this->assertNull($file->width);
        $this->assertNull($file->height);

        $file->setBaseFile('not_exists');
        $file->initWidthAndHeight();
        $this->assertNull($file->width);
        $this->assertNull($file->height);
    }

    public function testInitBaseFile()
    {
        $file = new File;

        $file->path = $this->mocks('test-400x300.jpg');
        $file->initBaseFile();
        $this->assertEquals($file->getBaseFile()->getFilename(), 'test-400x300.jpg');
    }

    public function testSetBaseFile()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertEquals($file->getBaseFile()->getFilename(), 'test-400x300.jpg');

        $file = new File;
        $file->setBaseFile(new BaseFile($this->mocks('test-400x300.jpg')));
        $this->assertEquals($file->getBaseFile()->getFilename(), 'test-400x300.jpg');

        $file = new File;
        $file->setBaseFile(new SplFileInfo($this->mocks('test-400x300.jpg')));
        $this->assertEquals($file->getBaseFile()->getFilename(), 'test-400x300.jpg');
    }

    public function testIsMime()
    {
        $file = new File;
        $this->assertFalse($file->isMime());
        $this->assertFalse($file->isMime('image'));
        $this->assertFalse($file->isMime('image', 'jpeg'));

        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertTrue($file->isMime('image'));
        $this->assertTrue($file->isMime('image', 'jpeg'));

        $this->assertFalse($file->isMime());
        $this->assertFalse($file->isMime('image', 'gif'));
        $this->assertFalse($file->isMime('plain', 'jpeg'));
        $this->assertFalse($file->isMime('plain', 'text'));
    }

    public function testIsExtension()
    {
        $file = new File;
        $this->assertFalse($file->isExtension());
        $this->assertFalse($file->isExtension('jpeg'));
        $this->assertFalse($file->isExtension(['jpeg', 'gif']));

        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertTrue($file->isExtension('jpg'));
        $this->assertTrue($file->isExtension('JPG'));
        $this->assertTrue($file->isExtension('Jpg'));
        $this->assertTrue($file->isExtension(['jpg', 'gif']));

        $this->assertFalse($file->isExtension());
        $this->assertFalse($file->isExtension('gif'));
        $this->assertFalse($file->isExtension(['jpeg', 'gif']));
    }

    public function testIsImage()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));
        $this->assertTrue($file->isImage());
        $this->assertFalse($file->isVideo());
        $this->assertFalse($file->isAudio());
        $this->assertFalse($file->isDocument());
    }

    public function testIsVideo()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test.mp4'));
        $this->assertFalse($file->isImage());
        $this->assertTrue($file->isVideo());
        $this->assertFalse($file->isAudio());
        $this->assertFalse($file->isDocument());
    }

    public function testIsAudio()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test.mp3'));
        $this->assertFalse($file->isImage());
        $this->assertFalse($file->isVideo());
        $this->assertTrue($file->isAudio());
        $this->assertFalse($file->isDocument());
    }

    public function testIsDocument()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test.xlsx'));
        $this->assertFalse($file->isImage());
        $this->assertFalse($file->isVideo());
        $this->assertFalse($file->isAudio());
        $this->assertTrue($file->isDocument());
    }

    public function testResizeWithInvalidSize()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertFalse($file->resize(''));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 400);
        $this->assertEquals($file->getHeight(), 300);
    }

    public function testResizeFromUndefined()
    {
        $file = new File;

        $this->assertFalse($file->resize('400x300'));
    }

    public function testResizeFromActualSize()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertTrue($file->resize('400x300'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 400);
        $this->assertEquals($file->getHeight(), 300);
    }

    public function testResizeStatic()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertTrue($file->resize('300xx300'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 300);
        $this->assertEquals($file->getHeight(), 300);
    }

    public function testResizeRegular()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertTrue($file->resize('100x100'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 100);
        $this->assertEquals($file->getHeight(), 75);
    }

    public function testResizeEnlarge()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $this->assertTrue($file->resize('800x600-'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 400);
        $this->assertEquals($file->getHeight(), 300);

        $this->assertTrue($file->resize('800x600+'));

        $file->initWidthAndHeight();
        $this->assertEquals($file->getWidth(), 800);
        $this->assertEquals($file->getHeight(), 600);
    }

    public function testCropFromUndefined()
    {
        $file = new File;
        $this->assertFalse($file->crop(100, 100));
    }

    public function testCrop()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $crop = $file->copy($this->mocks('crop1.jpg'));
        $this->assertTrue($crop->crop(100, 100));

        $crop->initWidthAndHeight();
        $this->assertEquals($crop->getWidth(), 100);
        $this->assertEquals($crop->getHeight(), 100);

        $crop = $file->copy($this->mocks('crop2.jpg'));
        $this->assertTrue($crop->crop(100, 100, 300, 0));
        $crop->initWidthAndHeight();
        $this->assertEquals($crop->getWidth(), 100);
        $this->assertEquals($crop->getHeight(), 100);

        $crop = $file->copy($this->mocks('crop3.jpg'));
        $this->assertTrue($crop->crop(100, 100, 300, 200));
        $crop->initWidthAndHeight();
        $this->assertEquals($crop->getWidth(), 100);
        $this->assertEquals($crop->getHeight(), 100);

        $crop = $file->copy($this->mocks('crop4.jpg'));
        $this->assertTrue($crop->crop(100, 100, 0, 200));
        $crop->initWidthAndHeight();
        $this->assertEquals($crop->getWidth(), 100);
        $this->assertEquals($crop->getHeight(), 100);
    }

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

    public function testIsActualSize()
    {
        // @todo
    }

    public function testUploadedSettings()
    {
        $uploadedFile = new UploadedFile($this->mocks('test-400x300.jpg'), 'original.jpg', null, $filesize = filesize($this->mocks('test-400x300.jpg')), UPLOAD_ERR_OK);

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

    public function testSizeToKey()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x300.jpg'));

        $resize = $file->getResize(['size' => '100x100']);
        $this->assertEquals($resize->key, '100x100');

        $resize = $file->getResize(['size' => '100xx100']);
        $this->assertEquals($resize->key, '100xx100');
    }

    public function testSanitizePath()
    {
        $file = new File;
        $file->setBaseFile($fullPath = base_path($relativePath = 'directory/image.jpg'));
        $this->assertEquals($file->path, $fullPath);

        $file->setBaseFile(public_path($relativePath));
        $this->assertEquals($file->path, $relativePath);
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
    }

    public function testIsSizeActual()
    {
        $file = new File;
        $file->setBaseFile($this->mocks('test-400x400.jpg'));

        $this->assertTrue($file->isSizeActual('400x400'));
        $this->assertTrue($file->isSizeActual('400xx400'));
        $this->assertTrue($file->isSizeActual('800x800'));

        $this->assertFalse($file->isSizeActual([]));
        $this->assertFalse($file->isSizeActual('400x300'));
        $this->assertFalse($file->isSizeActual('300x400'));
        $this->assertFalse($file->isSizeActual('800x800+'));
    }

    private function mocks($path = '')
    {
        return config('upload.path', __DIR__.'/../../../storage/app/public').($path ? '/'.$path : '');
    }
}
