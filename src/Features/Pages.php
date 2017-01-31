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
        $this->page = $this->getRequestAttribute('page', 1);
    }


    public function initPerPage()
    {
        $this->perPage = 20;
    }


    public function initPaginator()
    {
        $this->paginator = new LengthAwarePaginator($this->perPage, $this->getTotal(), $this->perPage, $this->page);
        $this->paginator->setPageName($this->getRequestAttributeName('page'));

        $url = urlbuilder($this->getUrl([ '!pages' ]));

        foreach ($url->query() as $key => $value) {
            $this->paginator->addQuery($key, $value);
        }

        $url->query(false);

        $this->paginator->setPath($url->compile());
    }


    protected function applyQueryPages($select)
    {
        if ( ! empty( $this->perPage )) {
            $select->forPage($this->page, $this->perPage);
        }

    }


    public function getPaginator()
    {
        if (is_null($this->paginator)) {
            $this->initPaginator();
        }

        return $this->paginator;
    }


    protected function applyUrlPages(&$url)
    {
        $url->query([ $this->getRequestAttributeName('page') => $this->page ]);
    }

}