<?php

namespace Dukhanin\Panel\Collections;

use Illuminate\Database\Eloquent\Collection;

class PanelModelsCollection extends Collection
{
    public $keyName = 'id';

    public $keyParentName = 'parent_id';

    protected $isTree = false;

    public function tree()
    {
        if ($this->isTree) {
            return clone $this;
        }

        $index = $this->buildTreeIndex();

        $rootKeys = array_diff(array_keys($index), array_flatten($index));

        $tree = new static;

        $tree->isTree = true;

        foreach ($rootKeys as $key) {
            $this->fillTreeCollectionRecursive($tree, $index, $key, 0);
        }

        return $tree;
    }

    public function isTree()
    {
        return $this->isTree;
    }

    public function options($key, $depthPrefix = null)
    {
        static $depth = 0;

        if (is_null($depthPrefix)) {
            $depthPrefix = '    ';
        }

        $options = collect();

        foreach ($this->items as $item) {
            $label = is_callable($key) ? $key($item) : array_get($item, $key);

            $options->put(array_get($item, $this->keyName), str_repeat($depthPrefix, $depth).$label);

            if ($this->isTree()) {
                $depth++;

                $item->nestedCollection()->options($key, $depthPrefix)->each(function ($option, $key) use (
                    $options,
                    $depth,
                    $depthPrefix
                ) {
                    $options->put($key, $option);
                });

                $depth--;
            }
        }

        return $options;
    }

    protected function fillTreeCollectionRecursive(Collection $collection, array $index, $parentKey, $depth)
    {
        if (! isset($index[$parentKey])) {
            return false;
        }

        foreach ($index[$parentKey] as $key) {
            $item = $this->first(function ($item) use ($key) {
                return array_get($item, $this->keyName) == $key;
            });

            $collection->put($key, $item);

            $item->nestedDepth($depth);

            $item->nestedCollection()->isTree = true;

            $this->fillTreeCollectionRecursive($item->nestedCollection(), $index, $key, $depth + 1);
        }
    }

    protected function buildTreeIndex()
    {
        $index = [];

        foreach ($this->items as $item) {
            $parentKey = intval(array_get($item, $this->keyParentName));

            if ($parentKey !== 0 && ! $this->contains($this->keyName, $parentKey)) {
                continue;
            }

            if (! isset($index[$parentKey])) {
                $index[$parentKey] = [];
            }

            $index[$parentKey][] = array_get($item, $this->keyName);
        }

        return $index;
    }
}