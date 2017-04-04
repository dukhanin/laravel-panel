<?php

namespace Dukhanin\Panel\Features;

trait Order
{
    protected $order;

    protected $orderDesc;

    public function initOrder()
    {
        $this->order = $this->input('order');
        $this->orderDesc = $this->input('orderDesc', false);
    }

    public function order()
    {
        if (is_null($this->order)) {
            $this->initOrder();
        }

        return $this->order;
    }

    public function orderDesc()
    {
        if (is_null($this->orderDesc)) {
            $this->initOrder();
        }

        return $this->orderDesc;
    }

    protected function applyQueryOrder($select)
    {
        $columns = $this->columns()->resolved();

        if (empty($columns[$this->order()]['order'])) {
            return;
        }

        $select->getQuery()->orders = null;

        if (is_bool($order = $columns[$this->order()]['order'])) {
            $order = $this->order();
        }

        if (! is_string($order) && is_callable($order)) {
            call_user_func($order, $select, $this);
        } else {
            $select->orderBy($order, $this->orderDesc() ? 'desc' : 'asc');
        }
    }

    protected function applyUrlOrder(&$url)
    {
        $query = [];

        if ($this->order()) {
            $query['order'] = $this->order;
        }

        if ($this->orderDesc()) {
            $query['orderDesc'] = 1;
        }

        $url = $url->query($query);
    }
}