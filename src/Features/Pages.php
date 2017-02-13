<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Pagination\LengthAwarePaginator;

trait Pages
{

    public $page;

    public $perPage;

    protected $paginator;


    public function initFeaturePages()
    {
        $this->initPage();
        $this->initPerPage();
    }


    public function initPage()
    {
        $this->page = $this->input('page', 1);
    }


    public function initPerPage()
    {
        $this->perPage = 20;
    }


    public function initPaginator()
    {
        $this->paginator = new LengthAwarePaginator($this->perPage(), $this->total(), $this->perPage(), $this->page());
        $this->paginator->setPageName('page');

        $url = urlbuilder($this->url([ '!pages' ]));

        $this->paginator->appends($url->query());
        $this->paginator->setPath($url->query(false)->compile());
    }


    public function perPage()
    {
        if (is_null($this->perPage)) {
            $this->initPerPage();
        }

        return $this->perPage;
    }


    public function page()
    {
        if (is_null($this->page)) {
            $this->initPage();
        }

        return $this->page;
    }


    public function paginator()
    {
        if (is_null($this->paginator)) {
            $this->initPaginator();
        }

        return $this->paginator;
    }


    protected function applyQueryPages($select)
    {
        if ( ! empty( $this->perPage )) {
            $select->forPage($this->page, $this->perPage);
        }

    }


    protected function applyUrlPages(&$url)
    {
        $url->query([ 'page' => $this->page ]);
    }

}