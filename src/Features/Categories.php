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
        $this->category = $this->getRequestAttribute('category');
    }


    public function getCategories()
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
            $url->query([ $this->getRequestAttributeName('category') => $this->category ]);
        }
    }

}