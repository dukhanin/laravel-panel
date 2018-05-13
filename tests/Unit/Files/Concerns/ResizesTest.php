<?php

namespace Dukhanin\Panel\Tests\Unit\Files;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\File as Filesystem;
use Dukhanin\Panel\Files\InvalidImageSourceException;
use Dukhanin\Panel\Files\InvalidSizeDefinitionException;
use Dukhanin\Panel\Tests\TestCase;
use Dukhanin\Panel\Files\File;

class ResizesTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        $this->createApplication();

        $this->faker = FakerFactory::create();
    }

    public function test_isSizeActual_returns_null_on_invalid_files()
    {
        foreach ($this->invalidImages() as $file) {
            $this->assertNull($file->isSizeActual('100x100'));
        }
    }

    public function test_isSizeActual_throws_an_exception_when_size_definition_is_not_valid()
    {
        $file = factory(File::class)->states('image')->make();

        $exceptionsCaught = 0;

        foreach ($cases = $this->invalidSizes() as $size) {
            try {
                $file->isSizeActual($size);
            } catch (InvalidSizeDefinitionException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($cases), $exceptionsCaught);
    }

    public function test_isSizeActual_defines_actuality_correctly()
    {
        $sources = [
            [50, 50],
            [100, 50],
            [50, 100],
        ];

        $cases = [
            '50xx50' => [true, false, false],
            '50x50+-' => [true, false, false],
            '50x50+' => [true, true, true],
            '50x50-' => [true, false, false],
            '50x50' => [true, false, false],

            '100xx100' => [false, false, false],
            '100x100+-' => [false, true, true],
            '100x100+' => [false, true, true],
            '100x100-' => [true, true, true],
            '100x100' => [true, true, true],

            '25xx25' => [false, false, false],
            '25x25+-' => [false, false, false],
            '25x25+' => [true, true, true],
            '25x25-' => [false, false, false],
            '25x25' => [false, false, false],

            '100xx50' => [false, true, false],
            '100x50+-' => [true, true, false],
            '100x50+' => [true, true, true],
            '100x50-' => [true, true, false],
            '100x50' => [true, true, false],

            '200xx100' => [false, false, false],
            '200x100+-' => [false, false, true],
            '200x100+' => [false, false, true],
            '200x100-' => [true, true, true],
            '200x100' => [true, true, true],

            '50xx25' => [false, false, false],
            '50x25+-' => [false, false, false],
            '50x25+' => [true, true, true],
            '50x25-' => [false, false, false],
            '50x25' => [false, false, false],

            '50xx100' => [false, false, true],
            '50x100+-' => [true, false, true],
            '50x100+' => [true, true, true],
            '50x100-' => [true, false, true],
            '50x100' => [true, false, true],

            '100xx200' => [false, false, false],
            '100x200+' => [false, true, false],
            '100x200-' => [true, true, true],
            '100x200' => [true, true, true],

            '25xx50' => [false, false, false],
            '25x50+' => [true, true, true],
            '25x50-' => [false, false, false],
            '25x50' => [false, false, false],
        ];

        foreach ($sources as $sourceKey => $sourceDimensions) {
            $file = factory(File::class)->states('image')->make([
                'width' => $sourceDimensions[0],
                'height' => $sourceDimensions[1],
            ]);

            foreach ($cases as $size => $expectations) {
                $expectingValue = $expectations[$sourceKey];

                $message = "Getting incorrect value when trying to check File::isSizeActual()";
                $message .= "\nDimensions: {$sourceDimensions[0]}x{$sourceDimensions[1]}, Checking size: {$size}";
                $message .= "\nExpecting value: ".($expectingValue ? 'true' : 'false');

                $this->assertEquals($file->isSizeActual($size), $expectingValue, $message);
            }
        }
    }

    public function test_resize_throws_an_exception_if_source_is_invalid()
    {
        $exceptionsCaught = 0;

        foreach ($invalidSources = $this->invalidImages() as $file) {
            try {
                $file->resize('200x100');
            } catch (InvalidImageSourceException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($invalidSources), $exceptionsCaught);
    }

    public function test_resize_doesnt_change_file_when_size_is_actual()
    {
        $file = factory(File::class)->states('image')->create([
            'width' => 400,
            'height' => 300,
        ]);

        $sizeBefore = $file->getSize();

        $this->assertFalse($file->resize('400x300'));
        $this->assertEquals($sizeBefore, $file->getSize());
    }

    public function test_resize_calculates_dimensions_correctly()
    {
        $sources = [
            'square' => [50, 50],
            'horizontal' => [100, 50],
            'vertical' => [50, 100],
        ];

        $cases = [
            'reduce to square' => [
                'size' => '40x40',
                'square' => [40, 40],
                'horizontal' => [40, 20],
                'vertical' => [20, 40],
            ],
            'reduce to horizontal' => [
                'size' => '40x20',
                'square' => [20, 20],
                'horizontal' => [40, 20],
                'vertical' => [10, 20],
            ],
            'reduce to vertical' => [
                'size' => '20x40',
                'square' => [20, 20],
                'horizontal' => [20, 10],
                'vertical' => [20, 40],
            ],
            'reduce to static square' => [
                'size' => '40xx40',
                'square' => [40, 40],
                'horizontal' => [40, 40],
                'vertical' => [40, 40],
            ],
            'reduce to static horizontal' => [
                'size' => '40xx20',
                'square' => [40, 20],
                'horizontal' => [40, 20],
                'vertical' => [40, 20],
            ],
            'reduce to static vertical' => [
                'size' => '20xx40',
                'square' => [20, 40],
                'horizontal' => [20, 40],
                'vertical' => [20, 40],
            ],
            'enlarge to square' => [
                'size' => '400x400+',
                'square' => [400, 400],
                'horizontal' => [400, 200],
                'vertical' => [200, 400],
            ],
            'enlarge to horizontal' => [
                'size' => '400x200+',
                'square' => [200, 200],
                'horizontal' => [400, 200],
                'vertical' => [100, 200],
            ],
            'enlarge to vertical' => [
                'size' => '200x400+',
                'square' => [200, 200],
                'horizontal' => [200, 100],
                'vertical' => [200, 400],
            ],
            'enlarge to static square' => [
                'size' => '400xx400+',
                'square' => [400, 400],
                'horizontal' => [400, 400],
                'vertical' => [400, 400],
            ],
            'enlarge to static horizontal' => [
                'size' => '400xx200+',
                'square' => [400, 200],
                'horizontal' => [400, 200],
                'vertical' => [400, 200],
            ],
            'enlarge to static vertical' => [
                'size' => '200xx400+',
                'square' => [200, 400],
                'horizontal' => [200, 400],
                'vertical' => [200, 400],
            ],
        ];

        foreach ($sources as $sourceName => $sourceDimensions) {
            foreach ($cases as $caseName => $case) {
                $size = $case['size'];
                $file = factory(File::class)->states('image')->make([
                    'width' => $sourceDimensions[0],
                    'height' => $sourceDimensions[1],
                ]);

                $file->resize($size);

                $expectingDimensions = $case[$sourceName];
                $message = "Getting incorrect resize dimensions when trying to";
                $message .= "\n{$sourceName} {$sourceDimensions[0]}x{$sourceDimensions[1]} {$caseName} {$size}";
                $message .= "\nExpectations: {$expectingDimensions[0]}x{$expectingDimensions[1]}, Actual: {$file->getWidth()}x{$file->getHeight()}";

                $this->assertEquals($expectingDimensions[0], $file->getWidth(), $message);
                $this->assertEquals($expectingDimensions[1], $file->getHeight(), $message);
            }
        }
    }

    public function test_createResize_throws_an_exception_if_source_is_invalid()
    {
        $exceptionsCaught = 0;

        foreach ($invalidSources = $this->invalidImages() as $file) {
            try {
                $file->createResize(['key' => 'small', 'size' => '100x100']);
            } catch (InvalidImageSourceException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($invalidSources), $exceptionsCaught);
    }

    public function test_createResize_throws_an_exception_if_size_definition_is_invalid()
    {
        $file = factory(File::class)->states('image')->make();

        $exceptionsCaught = 0;

        foreach ($cases = $this->invalidSizes() as $size) {
            try {
                $file->createResize($size);
            } catch (InvalidSizeDefinitionException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($cases), $exceptionsCaught);
    }

    public function test_createResize_returns_correct_resize()
    {
        $file = factory(File::class)->states('image')->make([
            'width' => 400,
            'height' => 300,
        ]);

        $resize = $file->createResize(['key' => 'small', 'size' => '100x100']);

        $this->assertNotEquals($file, $resize);
        $this->assertNotEquals($file->getPath(), $resize->getPath());
        $this->assertEquals(100, $resize->getWidth());
        $this->assertEquals(75, $resize->getHeight());
        $this->assertFileExists($resize->getPath());
    }

    public function test_hasResize()
    {
        $file = factory(File::class)->states('image')->create(['width' => 640, 'height' => 480]);
        $this->assertFalse($file->hasResize('small'));

        $file->getResize(['key' => 'small', 'size' => '100x100']);
        $this->assertTrue($file->hasResize('small'));
    }

    public function test_getResize_throws_exception_if_size_definition_is_invalid()
    {
        $file = factory(File::class)->states('image')->make();

        $cases = [
            ['key' => 'small', 'no_size_defined'],
            ['key' => 'small', 'size' => 'is_invalid'],
            '',
            false,
        ];

        $exceptionsCaught = 0;

        foreach ($cases as $options) {
            try {
                $file->getResize($options);
            } catch (InvalidSizeDefinitionException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($cases), $exceptionsCaught);
    }

    public function test_getResize_returns_fake_if_source_is_invalid()
    {
        foreach ($invalidSource = $this->invalidImages() as $source) {
            $resize = $source->getResize(['key' => 'small', 'size' => '200x100']);

            $this->assertEquals($resize->getFilename(), pathinfo(config('files.types.image.fake'), PATHINFO_BASENAME));
            $this->assertEquals($resize->getWidth(), 200);
            $this->assertEquals($resize->getHeight(), 100);
        }
    }

    public function test_getResize_loads_resize_if_it_already_exists()
    {
        $file = factory(File::class)->states('image')->create([
            'width' => 400,
            'height' => 300,
        ]);
        $resize = $file->getResize(['key' => 'small', 'size' => '200x200']);

        $resizeLoaded = $file->fresh()->getResize(['key' => 'small', 'size' => '100x100']);
        $this->assertEquals($resize->id, $resizeLoaded->id);
        $this->assertEquals('small', $resizeLoaded->key);
    }

    public function test_getResize_makes_correct_dimensions_for_resizes()
    {
        $file = factory(File::class)->states('image')->make([
            'width' => 400,
            'height' => 300,
        ]);

        $resize = $file->getResize(['key' => 'small', 'size' => '100x100']);
        $this->assertEquals('small', $resize->key);
        $this->assertEquals(100, $resize->getWidth());
        $this->assertEquals(75, $resize->getHeight());

        $resize = $file->getResize('200x200');
        $this->assertEquals('200x200', $resize->key);
        $this->assertEquals(200, $resize->getWidth());
        $this->assertEquals(150, $resize->getHeight());
    }

    public function test_getResize_updates_children_after_makes_a_new_child()
    {
        $file = factory(File::class)->states('image')->create([
            'width' => 640,
            'height' => 480,
        ]);
        $this->assertCount(0, $file->children);

        $resize = $file->getResize('100x100');

        $this->assertCount(1, $file->children);
        $this->assertEquals($resize->id, $file->children->first()->id);
    }

    public function test_getResize_with_force_mode_creates_new_resize()
    {
        $file = factory(File::class)->states('image')->make([
            'width' => 400,
            'height' => 300,
        ]);
        $file->getResize(['key' => 'small', 'size' => '200x200']);

        $forcedResize = $file->getResize(['key' => 'small', 'size' => '100x100', 'force' => true]);

        $this->assertEquals('small', $forcedResize->key);
        $this->assertEquals(100, $forcedResize->getWidth());
        $this->assertEquals(75, $forcedResize->getHeight());
    }

    public function test_getResize_updates_children_after_creating_new_child()
    {
        $file = factory(File::class)->states('image')->create([
            'width' => 640,
            'height' => 480,
        ]);
        $this->assertCount(0, $file->children);

        $resize = $file->getResize('100x100');

        $this->assertCount(1, $file->children);
        $this->assertEquals($resize->id, $file->children->first()->id);
    }

    public function test_getResizes_appends_correct_keys_to_filename()
    {
        $cases = [
            '-200x100' => [
                '200x100',
                ['size' => '200x100'],
                ['size' => ['width' => 200, 'height' => 100]],
            ],
            '-200xx100' => [
                '200xx100',
                ['size' => '200xx100'],
                ['size' => ['width' => 200, 'height' => 100, 'static' => true]],
            ],
            'small' => [
                ['key' => 'small', 'size' => '200x100'],
                ['key' => 'small', 'size' => ['width' => 200, 'height' => 100, 'static' => true]],
            ],
        ];

        $file = factory(File::class)->states('image')->make();

        foreach ($cases as $filenameEndsWith => $options) {
            foreach ($options as $argument) {
                $resize = $file->getResize($argument);
                $filename = pathinfo($resize->getBasename(), PATHINFO_FILENAME);
                $resizeToString = json_encode($argument);
                $message = "Filename ({$filename}) does not end with {$filenameEndsWith} after resize {$resizeToString} has been created";

                $this->assertStringEndsWith($filenameEndsWith, $filename, $message);
            }
        }
    }

    public function test_crop_throws_an_exception_if_source_is_invalid()
    {
        $exceptionsCaught = 0;

        foreach ($cases = $this->invalidImages() as $file) {
            try {
                $file->crop(100, 100);
            } catch (InvalidImageSourceException $e) {
                $exceptionsCaught++;
            }
        }

        $this->assertEquals(count($cases), $exceptionsCaught);
    }

    public function test_crop_makes_identical_images_if_arguments_are_equals()
    {
        $file = factory(File::class)->states('image')->make();

        $resize1 = $file->getResize(['key' => 'one', 'size' => '100xx100']);
        $resize2 = $file->getResize(['key' => 'two', 'size' => '100xx100']);

        $resize1->crop(50, 20, 10, 10);
        $resize2->crop(50, 20, 10, 10);

        $this->assertEquals($resize1->size, $resize2->size);
    }

    public function test_crop_makes_different_images_if_x_and_y_differs()
    {
        $file = factory(File::class)->states('image')->make();

        $resize1 = $file->getResize(['key' => 'one', 'size' => '100xx100']);
        $resize2 = $file->getResize(['key' => 'two', 'size' => '100xx100']);

        $resize1->crop(50, 20, 10, 10);
        $resize2->crop(50, 20, 50, 50);

        $this->assertNotEquals($resize1->size, $resize2->size);
    }

    public function test_crop_sets_dimensions_correctly()
    {
        $files = factory(File::class, 2)->states('image')->make([
            'width' => 200,
            'height' => 100,
        ]);

        $files[0]->crop(50, 20);
        $this->assertEquals(50, $files[0]->getWidth());
        $this->assertEquals(20, $files[0]->getHeight());

        $files[1]->crop(50, 20, 10, 10);
        $this->assertEquals(50, $files[1]->getWidth());
        $this->assertEquals(20, $files[1]->getHeight());
    }

    public function test_crop_updates_filesize()
    {
        $file = factory(File::class)->states('image')->make([
            'width' => 200,
            'height' => 100,
        ]);

        $filesizeBefore = $file->size;

        $file->crop(50, 20);

        $this->assertNotNull($filesizeBefore);
        $this->assertNotEquals($filesizeBefore, $file->size);
    }

    public function test_crop_sets_settings()
    {
        $file = factory(File::class)->states('image')->make([
            'width' => 200,
            'height' => 100,
        ]);

        $file->crop(50, 20, 30, 40);

        $this->assertArraySubset([
            'w' => 50,
            'h' => 20,
            'x' => 30,
            'y' => 40,
        ], $file->settings['crop']['area']);
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

    protected function invalidSizes()
    {
        return ['', '0x0', '20x0', '0x20', '100,100', 'x', false];
    }
}