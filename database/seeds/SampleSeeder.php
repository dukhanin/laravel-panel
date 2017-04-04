<?php

use Illuminate\Database\Seeder;

use App\Sample\Section;
use App\Sample\Product;
use Dukhanin\Panel\Files\File;

class SampleSeeder extends Seeder
{
    protected $plants;

    protected $fruits;

    protected $vegetables;

    protected $bakery;

    protected $bread;

    protected $beverages;

    public function run()
    {
        $this->seedSections();

        $this->seedProducts();

        $this->seedFiles();
    }

    protected function seedSections()
    {
        $this->plants = Section::create([
            'name' => 'Plants',
            'description' => '',
            'enabled' => 1,
            'image' => 56,
            'index' => 0,
            'parent_id' => null,
        ]);

        $this->fruits = Section::create([
            'name' => 'Fruits',
            'description' => '',
            'enabled' => 1,
            'image' => 65,
            'index' => 0,
            'parent_id' => $this->plants->getKey(),
        ]);

        $this->vegetables = Section::create([
            'name' => 'Vegetables',
            'description' => '',
            'enabled' => 1,
            'image' => 68,
            'index' => 1,
            'parent_id' => $this->plants->getKey(),
        ]);

        $this->bakery = Section::create([
            'name' => 'Bakery',
            'description' => '',
            'enabled' => 1,
            'image' => 53,
            'index' => 1,
            'parent_id' => null,
        ]);

        $this->bread = Section::create([
            'name' => 'Bread',
            'description' => '',
            'enabled' => 1,
            'image' => 59,
            'index' => 0,
            'parent_id' => $this->bakery->getKey(),
        ]);

        $this->beverages = Section::create([
            'name' => 'Beverages',
            'description' => '',
            'enabled' => 1,
            'image' => 62,
            'index' => 2,
            'parent_id' => null,
        ]);
    }

    protected function seedProducts()
    {
        Product::create([
            'name' => 'Apple',
            'delivired' => '1987-10-27',
            'description' => '<p>Green and fresh</p>',
            'enabled' => 1,
            'images' => ["3", "5", "1"],
            'index' => 0,
            'section_id' => $this->fruits->getKey(),
        ]);

        Product::create([
            'name' => 'Pear',
            'delivired' => '2015-03-12',
            'description' => '<p>Yellow and crispy</p>',
            'enabled' => 1,
            'images' => ["10", "11"],
            'index' => 1,
            'section_id' => $this->fruits->getKey(),
        ]);

        Product::create([
            'name' => 'Carrot',
            'delivired' => '2012-05-15',
            'description' => '<p>Orange and long</p>',
            'enabled' => 1,
            'images' => ["15"],
            'index' => 0,
            'section_id' => $this->vegetables->getKey(),
        ]);

        Product::create([
            'name' => 'Potatoe',
            'delivired' => '2017-03-19',
            'description' => '<p>Round and heavy</p>',
            'enabled' => 1,
            'images' => ["18", "19"],
            'index' => 1,
            'section_id' => $this->vegetables->getKey(),
        ]);

        Product::create([
            'name' => 'Baguette',
            'delivired' => '1992-05-17',
            'description' => '<p>So French!</p>',
            'enabled' => 1,
            'images' => ["23", "24"],
            'index' => 0,
            'section_id' => $this->bread->getKey(),
        ]);

        Product::create([
            'name' => 'Bun',
            'delivired' => '1987-10-27',
            'description' => '<p>Just baked</p>',
            'enabled' => 1,
            'images' => ["43", "44"],
            'index' => 1,
            'section_id' => $this->bread->getKey(),
        ]);

        Product::create([
            'name' => 'Milk',
            'delivired' => '2010-04-01',
            'description' => '<p>From best cows</p>',
            'enabled' => 1,
            'images' => ["38", "39"],
            'index' => 0,
            'section_id' => $this->beverages->getKey(),
        ]);

        Product::create([
            'name' => 'Orange juice',
            'delivired' => '1992-10-12',
            'description' => '<p>Tasty!</p>',
            'enabled' => 1,
            'images' => ["48", "49"],
            'index' => 1,
            'section_id' => $this->beverages->getKey(),
        ]);
    }

    protected function seedFiles()
    {
        $file = new File;
        $file->id = 56;
        $file->setBaseFile(upload()->path('sections/plants.jpg'));
        $file->save();

        $file = new File;
        $file->id = 65;
        $file->setBaseFile(upload()->path('sections/1468285413814-1.jpg'));
        $file->save();

        $file = new File;
        $file->id = 68;
        $file->setBaseFile(upload()->path('sections/vegetable-mix.jpg'));
        $file->save();

        $file = new File;
        $file->id = 53;
        $file->setBaseFile(upload()->path('sections/bread-2.png'));
        $file->save();

        $file = new File;
        $file->id = 59;
        $file->setBaseFile(upload()->path('sections/bread.jpg'));
        $file->save();

        $file = new File;
        $file->id = 62;
        $file->setBaseFile(upload()->path('sections/beverages.jpg'));
        $file->save();

        $file = new File;
        $file->id = 1;
        $file->setBaseFile(upload()->path('products/apple-green.jpg'));
        $file->save();

        $file = new File;
        $file->id = 3;
        $file->setBaseFile(upload()->path('products/apple-red.jpg'));
        $file->save();

        $file = new File;
        $file->id = 5;
        $file->setBaseFile(upload()->path('products/apple-yellow.gif'));
        $file->save();

        $file = new File;
        $file->id = 10;
        $file->setBaseFile(upload()->path('products/pear-red.jpg'));
        $file->save();

        $file = new File;
        $file->id = 11;
        $file->setBaseFile(upload()->path('products/pear-green.jpg'));
        $file->save();

        $file = new File;
        $file->id = 15;
        $file->setBaseFile(upload()->path('products/carrot.jpg'));
        $file->save();

        $file = new File;
        $file->id = 18;
        $file->setBaseFile(upload()->path('products/patatoe-1.jpg'));
        $file->save();

        $file = new File;
        $file->id = 19;
        $file->setBaseFile(upload()->path('products/patatoe-2.gif'));
        $file->save();

        $file = new File;
        $file->id = 23;
        $file->setBaseFile(upload()->path('products/baguette-1.jpg'));
        $file->save();

        $file = new File;
        $file->id = 24;
        $file->setBaseFile(upload()->path('products/baguette-2.jpg'));
        $file->save();

        $file = new File;
        $file->id = 43;
        $file->setBaseFile(upload()->path('products/bun-2.jpg'));
        $file->save();

        $file = new File;
        $file->id = 44;
        $file->setBaseFile(upload()->path('products/bun-1.jpg'));
        $file->save();

        $file = new File;
        $file->id = 38;
        $file->setBaseFile(upload()->path('products/milk-1.jpg'));
        $file->save();

        $file = new File;
        $file->id = 39;
        $file->setBaseFile(upload()->path('products/milk-2.jpg'));
        $file->save();

        $file = new File;
        $file->id = 48;
        $file->setBaseFile(upload()->path('products/juice-2.jpg'));
        $file->save();

        $file = new File;
        $file->id = 49;
        $file->setBaseFile(upload()->path('products/juice-1.jpg'));
        $file->save();
    }
}
