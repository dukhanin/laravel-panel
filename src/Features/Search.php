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
        $this->search = $this->input('search');
    }

    protected function applyQuerySearch($select)
    {
        // @todo
    }

    protected function applyUrlSearch(&$url)
    {
        if (trim($this->search)) {
            $url->query(['search' => $this->search]);
        }
    }
}