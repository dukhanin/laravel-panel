<?php

namespace App\Sample;

use Dukhanin\Panel\Traits\PanelModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Section extends EloquentModel
{

    use PanelModel;

    protected $table = 'sample_sections';

    protected $fillable = [
        'name',
        'image',
        'description',
        'enabled',
        'parent_id'
    ];


    public function scopeOrderedDefault($query)
    {
        $query->orderBy('index', 'asc');
    }
    /*

        public static function initProductsCountForSections($collection)
        {
            $sectionsIds = $collection->pluck('id');

            if ($sectionsIds->isEmpty()) {
                return;
            }

            $counts = Product::select('section_id', DB::raw('count(*) as c'))->whereIn('section_id',
                $sectionsIds)->groupBy('section_id')->get()->pluck('c', 'section_id');

            foreach ($counts as $sectionId => $count) {
                $collection[$sectionId]->productsCount = $count;
            }


        }*/
}