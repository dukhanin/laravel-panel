<?php
namespace Dukhanin\Panel\Tests\Unit\Files\Concerns;

use Faker\Factory as FakerFactory;
use Dukhanin\Panel\Tests\TestCase;
use Dukhanin\Panel\Files\File;

class DimensionsTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        $this->createApplication();

        $this->faker = FakerFactory::create();
    }

    public function test_getWidth_returns_correct_width()
    {
        $file = factory(File::class)->states('image')->make(['width' => 200]);

        $this->assertEquals($file->getWidth(), 200);
    }

    public function test_getHeight_returns_correct_height()
    {
        $file = factory(File::class)->states('image')->make(['height' => 100]);

        $this->assertEquals($file->getHeight(), 100);
    }

    public function test_getWidth_returns_null_on_invalid_images()
    {
        foreach ($this->invalidImages() as $file) {
            $this->assertNull($file->getWidth());
        }
    }

    public function test_getHeight_returns_null_on_invalid_images()
    {
        foreach ($this->invalidImages() as $file) {
            $this->assertNull($file->getHeight());
        }
    }

    public function test_initWidthAndHeight_gets_correct_dimensions()
    {
        $image = factory(File::class)->states('image')->make(['width' => 400, 'height' => 300]);

        $file = factory(File::class)->make();
        $file->setBaseFile($image->getPath());

        $this->assertEquals(400, $file->getWidth());
        $this->assertEquals(300, $file->getHeight());
    }

    protected function invalidImages()
    {
        return [
            factory(File::class)->make(),
            factory(File::class)->states('defined')->make(),
            factory(File::class)->states('document')->make(),
            factory(File::class)->states('video')->make(),
            factory(File::class)->states('audio')->make(),
        ];
    }
}
