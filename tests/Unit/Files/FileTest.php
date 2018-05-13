<?php

namespace Dukhanin\Panel\Tests\Unit\Files;

use Faker\Factory as FakerFactory;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as BaseFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Dukhanin\Panel\Tests\TestCase;
use Dukhanin\Panel\Files\File;

class FileTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        $this->createApplication();

        $this->faker = FakerFactory::create();
    }

    public function test_config_is_not_empty()
    {
        $this->assertNotEmpty(config('files.types.image'));
    }

    public function test_setBaseFile_resolves_upload_path()
    {
        $file = factory(File::class)->make();

        $file->setBaseFile(rtrim(config('upload.path'), '/').'/hello/world.jpg');

        $this->assertEquals(trim(config('upload.url'), '/').'/hello/world.jpg', $file->path);
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->url);
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->url());
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->getUrl());
    }

    public function test_setBaseFile_resolves_public_path()
    {
        $file = factory(File::class)->make();

        $file->setBaseFile(public_path('hello/world.jpg'));

        $this->assertEquals('hello/world.jpg', $file->path);
        $this->assertEquals('/hello/world.jpg', $file->url);
        $this->assertEquals('/hello/world.jpg', $file->url());
        $this->assertEquals('/hello/world.jpg', $file->getUrl());
    }

    public function test_setBaseFile_does_not_resolve_foreign_path()
    {
        $file = factory(File::class)->make();

        $file->setBaseFile('/var/hello/world.jpg');

        $this->assertEquals('/var/hello/world.jpg', $file->path);
        $this->assertEquals('/var/hello/world.jpg', $file->url);
        $this->assertEquals('/var/hello/world.jpg', $file->url());
        $this->assertEquals('/var/hello/world.jpg', $file->getUrl());
    }

    public function test_setBaseFile_resolves_relative_public_path()
    {
        $file = factory(File::class)->make();

        $file->setBaseFile('hello/world.jpg');

        $this->assertEquals('hello/world.jpg', $file->path);
        $this->assertEquals(public_path('hello/world.jpg'), $file->getPath());
        $this->assertEquals('/hello/world.jpg', $file->url);
        $this->assertEquals('/hello/world.jpg', $file->url());
        $this->assertEquals('/hello/world.jpg', $file->getUrl());
    }

    public function test_setBaseFile_resolves_relative_upload_path()
    {
        $file = factory(File::class)->make();

        $file->setBaseFile(trim(config('upload.url'), '/').'/hello/world.jpg');

        $this->assertEquals(trim(config('upload.url'), '/').'/hello/world.jpg', $file->path);
        $this->assertEquals(rtrim(config('upload.path'), '/').'/hello/world.jpg', $file->getPath());
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->url);
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->url());
        $this->assertEquals(rtrim(config('upload.url'), '/').'/hello/world.jpg', $file->getUrl());
    }

    public function test_getPath_returns_null_when_file_is_undefined()
    {
        $file = factory(File::class)->create();

        $this->assertNull($file->getPath());
    }

    public function test_children_if_there_are_no_associated_files()
    {
        $file = factory(File::class)->make();

        $this->assertInstanceOf(HasMany::class, $file->children());
        $this->assertInstanceOf(Collection::class, $file->children);
        $this->assertCount(0, $file->children);
    }

    public function test_children_if_there_are_associated_files()
    {
        $file = factory(File::class)->create();
        $child = factory(File::class)->create([
            'parent_id' => $file->id,
        ]);

        $this->assertInstanceOf(HasMany::class, $file->children());
        $this->assertInstanceOf(Collection::class, $file->children);
        $this->assertCount(1, $file->children);
        $this->assertEquals($child->id, $file->children->first()->id);
    }

    public function test_parent_returns_null_if_it_is_empty()
    {
        $file = factory(File::class)->create();

        $this->assertInstanceOf(BelongsTo::class, $file->parent());
        $this->assertNull($file->parent);
    }

    public function test_parent_returns_file_if_it_has_been_set()
    {
        $parent = factory(File::class)->create();
        $file = factory(File::class)->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $file->parent());
        $this->assertInstanceOf(File::class, $file->parent);
        $this->assertEquals($parent->id, $file->parent->id);
    }

    public function test_getBaseFile_returns_correct_BaseFile_object_for_defined_file()
    {
        $file = factory(File::class)->states('exists')->make();
        $this->assertInstanceOf(BaseFile::class, $file->getBaseFile());
        $this->assertStringEndsWith($file->getFilename(), $file->path);
    }

    public function test_getBaseFile_returns_correct_BaseFile_object_for_undefined_file()
    {
        $file = factory(File::class)->make();

        $this->assertInstanceOf(BaseFile::class, $file->getBaseFile());
        $this->assertEmpty($file->getBaseFile()->getFilename());
    }

    public function test_initBaseFile_sets_correct_BaseFile_object()
    {
        $filepath = factory(File::class)->states('exists')->make()->getPath();
        $fileWithAbsolutelyPath = factory(File::class)->make([
            'path' => $filepath,
        ]);

        $fileWithRelativePath = factory(File::class)->make([
            'path' => preg_replace('#^('.preg_quote(config('upload.path')).')/*#', trim(config('upload.url'), '/').'/',
                $filepath),
        ]);

        $this->assertEquals($filepath, $fileWithRelativePath->getBaseFile()->getPathname());
        $this->assertEquals($filepath, $fileWithAbsolutelyPath->getBaseFile()->getPathname());
    }

    public function test_initBaseFile_sets_correct_BaseFile_object_for_undefined_path()
    {
        $file = factory(File::class)->make();

        $this->assertEmpty($file->getBaseFile()->getPathname());
    }

    public function test_setBaseFile_handles_BaseFile_object()
    {
        $file = factory(File::class)->make();
        $filepath = factory(File::class)->states('exists')->make()->getPath();
        $baseFile = new BaseFile($filepath);

        $file->setBaseFile($baseFile);

        $this->assertEquals($baseFile->getPathname(), $file->getPath());
        $this->assertEquals($baseFile, $file->getBaseFile());
    }

    public function test_setBaseFile_handles_filepath_string()
    {
        $file = factory(File::class)->make();
        $filepath = factory(File::class)->states('exists')->make()->getPath();

        $file->setBaseFile($filepath);

        $this->assertEquals($filepath, $file->getPath());
        $this->assertInstanceOf(BaseFile::class, $file->getBaseFile());
    }

    public function test_isDefined()
    {
        $file = factory(File::class)->make();
        $this->assertFalse($file->isDefined());

        $file = factory(File::class)->states('defined')->make();
        $this->assertTrue($file->isDefined());
    }

    public function test_isExists()
    {
        $file = factory(File::class)->make();
        $this->assertFalse($file->isExists());

        $file = factory(File::class)->states('defined')->make();
        $this->assertFalse($file->isExists());

        $file = factory(File::class)->states('exists')->make();
        $this->assertTrue($file->isExists());
    }

    public function test_delete()
    {
        $file = factory(File::class)->states('exists')->create();
        $filepath = $file->getPath();

        $file->delete();

        $this->assertNull($file->fresh());
        $this->assertFileNotExists($filepath);
    }

    public function test_delete_deletes_children()
    {
        $file = factory(File::class)->states('exists')->create();
        $children = factory(File::class, 2)->states('exists')->create([
            'parent_id' => $file->id,
        ]);

        $file->delete();

        $this->assertNull($children[0]->fresh());
        $this->assertNull($children[1]->fresh());
        $this->assertNotNull($children[0]->getPath());
        $this->assertNotNull($children[1]->getPath());
        $this->assertFileNotExists($children[0]->getPath());
        $this->assertFileNotExists($children[1]->getPath());
    }

    public function test_remove()
    {
        $file = factory(File::class)->states('exists')->create();
        $filepath = $file->getPath();

        $file->remove();

        $this->assertNotNull($file->fresh());
        $this->assertFileNotExists($filepath);
    }

    public function test_copy()
    {
        $file = factory(File::class)->states('exists')->make();

        $newFile = $file->copy(config('upload.path').'/hello.world');

        $this->assertFileExists($newFile->getPath());
        $this->assertNotEquals($file->getPath(), $newFile->getPath());
        $this->assertEquals($file->getSize(), $newFile->getSize());
    }

    public function test_move()
    {
        $file = factory(File::class)->states('exists')->make();
        $oldFilepath = $file->getPath();

        $file->move($newFilepath = config('upload.path').'/hello.world');

        $this->assertFileNotExists($oldFilepath);
        $this->assertFileExists($newFilepath);
        $this->assertEquals($file->getPath(), $newFilepath);
    }

    public function test_img_returns_empty_string_if_file_is_not_an_image()
    {
        $file = factory(File::class)->make();

        $this->assertEmpty($file->img());
    }

    public function test_img_returns_tag_if_file_defined()
    {
        $file = factory(File::class)->states('image')->make();

        $this->assertRegExp(":<img.*?src='".preg_quote($file->getUrl())."':", $file->img());
    }

    public function test_jsonSerialize()
    {
        $file = factory(File::class)->states('defined')->make();

        $this->assertArraySubset([
            'url' => $file->getUrl(),
            'is_image' => $file->isImage(),
            'is_video' => $file->isVideo(),
            'is_audio' => $file->isAudio(),
            'is_document' => $file->isDocument(),
            'children' => [],
        ], $file->jsonSerialize());
    }

    public function test_jsonSerialize_includes_children()
    {
        $file = factory(File::class)->states('defined')->create();
        $children = factory(File::class, 2)->states('defined')->create([
            'parent_id' => $file->id,
        ]);

        $this->assertCount(2, $file->jsonSerialize()['children']);
        $this->assertArraySubset([
            'children' => [
                0 => ['url' => $children[0]->getUrl()],
                1 => ['url' => $children[1]->getUrl()],
            ],
        ], $file->jsonSerialize());
    }

    public function test_sleep_excludes_baseFile_attribute()
    {
        $file = factory(File::class)->states('defined')->make();
        $this->assertInstanceOf(BaseFile::class, $baseFile = $file->getBaseFile());

        $this->assertArrayNotHasKey('baseFile', $file->__sleep());
    }

    public function test_setBaseFile_saves_uploadFile_attributes()
    {
        $file = factory(File::class)->states('image')->make([
            'ext' => 'jpg',
        ]);

        $file->setBaseFile(new UploadedFile($file->getPath(), $file->getBasename(), 'image/jpeg', 12345));

        $this->assertArraySubset([
            'upload_info' => [
                'name' => $file->getBasename(),
                'type' => 'image/jpeg',
                'size' => 12345,
            ],
        ], $file->settings);
    }

    public function test_attr_returns_attributes_for_image()
    {
        $image = factory(File::class)->states('image')->make([
            'width' => 200,
            'height' => 100,
        ]);

        $this->assertTrue(str_contains($image->attr(), "width='200'"));
        $this->assertTrue(str_contains($image->attr(), "height='100'"));
        $this->assertTrue(str_contains($image->attr(), "src='{$image->getUrl()}'"));
    }

    public function test_attr_overrides_attributes()
    {
        $image = factory(File::class)->states('image')->make([
            'width' => 200,
            'height' => 100,
        ]);

        $this->assertTrue(str_contains($image->attr(['width' => 50]), "width='50'"));
        $this->assertTrue(str_contains($image->attr(['hello' => 'world']), "hello='world'"));
    }

    public function test_attr_returns_empty_string_for_undefined()
    {
        $image = factory(File::class)->make();

        $this->assertEquals('', $image->attr());
    }
}
