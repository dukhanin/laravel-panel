<?php

namespace Dukhanin\Panel\Features;

trait Categories
{

    public $category;

    protected $categories;


    public function initFeatureCategories()
    {
        $this->initCategory();
    }


    public function initCategories()
    {
        $this->categories = [ ];
    }


    public function initCategory()
    {
        $this->category = $this->input('category');
    }


    public function categories()
    {
        if (is_null($this->categories)) {
            $this->initCategories();
        }

        return $this->categories;
    }


    protected function applyQueryCategories($select)
    {
        // extendable
    }


    protected function applyUrlCategory(&$url)
    {
        if ( ! empty( $this->category )) {
            $url->query([ 'category' => $this->category ]);
        }
    }

}