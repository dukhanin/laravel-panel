<?php

namespace Dukhanin\Panel\Files;

use Illuminate\Database\Eloquent\Builder;

class FilesQueryBuilder extends Builder
{
    public function findManyOrdered($ids, $columns = ['*'])
    {
        return parent::findMany($ids, $columns)->sortBy(function ($file) use ($ids) {
            return array_search($file->getKey(), (array) $ids);
        });
    }
}