<?php

namespace Dukhanin\Panel\Features;

trait Search
{

    public $search;


    public function initFeatureSearch()
    {
        $this->initSearch();
    }


    public function initSearch()
    {
        $this->search = $this->getRequestAttribute('search');
    }


    protected function applyQuerySearch($select)
    {
        // @todo
    }


    protected function applyUrlSearch(&$url)
    {
        if (trim($this->search)) {
            $url->query([ $this->getRequestAttributeName('search') => $this->search ]);
        }
    }

}