<?php

namespace Dukhanin\Panel\Features;

use Illuminate\Pagination\LengthAwarePaginator;

trait Pages
{
    public $page;

    public $perPage;

    public $perPageOptions;

    protected $paginator;

    public function initPage()
    {
        $this->page = $this->input('page', 1);
    }

    public function initPerPage()
    {
        $this->perPage = 20;
    }

    public function initPerPageOptions()
    {
        $this->perPageOptions = [20, 50, 'all'];
    }

    public function initPaginator()
    {
        $this->paginator = new LengthAwarePaginator($this->perPage(), $this->total(), $this->perPage(), $this->page());
        $this->paginator->setPageName('page');

        $url = urlbuilder($this->url(['!page']));

        $this->paginator->appends($url->query());
        $this->paginator->setPath($url->query(false)->compile());
    }

    public function perPage()
    {
        if (is_null($this->perPage)) {
            if (in_array($customPerPage = $this->input('perPage'), $this->perPageOptions())) {
                $this->perPage = $customPerPage;
            } else {
                $this->initPerPage();
            }
        }

        return $this->perPage;
    }

    public function perPageOptions()
    {
        if (is_null($this->perPageOptions)) {
            $this->initPerPageOptions();
        }

        return $this->perPageOptions;
    }

    public function page()
    {
        if (is_null($this->page)) {
            $this->initPage();
        }

        return $this->page;
    }

    public function offset()
    {
        return ($this->page() - 1) * $this->perPage();
    }

    public function paginator()
    {
        if (is_null($this->paginator) && $this->perPage() > 0) {
            $this->initPaginator();
        }

        return $this->paginator;
    }

    protected function applyQueryPage($select)
    {
        if ($this->perPage() > 0) {
            $select->forPage($this->page(), $this->perPage());
        }
    }

    protected function applyUrlPage(&$url)
    {
        $url->query(['page' => $this->page()]);
    }

    protected function applyUrlPerPage(&$url)
    {
        $url->query(['perPage' => $this->perPage()]);
    }
}