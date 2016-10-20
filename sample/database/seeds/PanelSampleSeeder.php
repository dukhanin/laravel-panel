<?php

use Illuminate\Database\Seeder;

use App\Panel\Sample\Section;
use App\Panel\Sample\Product;

class PanelSampleSeeder extends Seeder
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
    }


    protected function seedSections()
    {
        $this->plants = Section::create([
            'name'        => 'Plants',
            'description' => '',
            'enabled'     => 1,
            'index'       => 0,
            'parent_id'   => 0,
        ]);

        $this->fruits = Section::create([
            'name'        => 'Fruits',
            'description' => '',
            'enabled'     => 1,
            'index'       => 0,
            'parent_id'   => $this->plants->getKey()
        ]);

        $this->vegetables = Section::create([
            'name'        => 'Vegetables',
            'description' => '',
            'enabled'     => 1,
            'index'       => 1,
            'parent_id'   => $this->plants->getKey()
        ]);

        $this->bakery = Section::create([
            'name'        => 'Bakery',
            'description' => '',
            'enabled'     => 1,
            'index'       => 1,
            'parent_id'   => 0,
        ]);

        $this->bread = Section::create([
            'name'        => 'Bread',
            'description' => '',
            'enabled'     => 1,
            'index'       => 0,
            'parent_id'   => $this->bakery->getKey()
        ]);

        $this->beverages = Section::create([
            'name'        => 'Beverages',
            'description' => '',
            'enabled'     => 1,
            'index'       => 2,
            'parent_id'   => 0,
        ]);
    }


    protected function seedProducts()
    {
        Product::create([
            'name'        => 'Apple',
            'description' => '<p>Green and fresh</p>',
            'enabled'     => 1,
            'index'       => 0,
            'section_id'  => $this->fruits->getKey()
        ]);

        Product::create([
            'name'        => 'Pear',
            'description' => '<p>Yellow and crispy</p>',
            'enabled'     => 1,
            'index'       => 1,
            'section_id'  => $this->fruits->getKey()
        ]);

        Product::create([
            'name'        => 'Carrot',
            'description' => '<p>Orange and long</p>',
            'enabled'     => 1,
            'index'       => 0,
            'section_id'  => $this->vegetables->getKey()
        ]);

        Product::create([
            'name'        => 'Potatoe',
            'description' => '<p>Round and heavy</p>',
            'enabled'     => 1,
            'index'       => 1,
            'section_id'  => $this->vegetables->getKey()
        ]);

        Product::create([
            'name'        => 'Baguette',
            'description' => '<p>So French!</p>',
            'enabled'     => 1,
            'index'       => 0,
            'section_id'  => $this->bread->getKey()
        ]);

        Product::create([
            'name'        => 'Bun',
            'description' => '<p>Just baked</p>',
            'enabled'     => 1,
            'index'       => 1,
            'section_id'  => $this->bread->getKey()
        ]);

        Product::create([
            'name'        => 'Milk',
            'description' => '<p>From best cows</p>',
            'enabled'     => 1,
            'index'       => 0,
            'section_id'  => $this->beverages->getKey()
        ]);

        Product::create([
            'name'        => 'Orange juice',
            'description' => '<p>Tasty!</p>',
            'enabled'     => 1,
            'index'       => 1,
            'section_id'  => $this->beverages->getKey()
        ]);
    }
}
