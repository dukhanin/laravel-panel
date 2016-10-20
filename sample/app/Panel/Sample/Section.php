<?php

namespace App\Panel\Sample;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Section extends EloquentModel
{

    protected $table = 'panel_sample_sections';

    protected $fillable = [
        'name',
        'description',
        'enabled',
        'parent_id'
    ];


    public function scopeOrdered($query)
    {
        $query->orderBy('index', 'asc');
    }


    public function scopeByParent($query, $parentId = 0)
    {
        if ($parentId instanceof Section) {
            $parentId = $parentId->getKey();
        }

        $query->where('parent_id', intval($parentId));
    }


    public function scopeByParentRecursive($query, $parentId = 0)
    {
        if ($parentId instanceof Section) {
            $parentId = $parentId->getKey();
        }

        $sectionsIds = static::collectChildSections([ 'parent_id' => $parentId ])->pluck('id')->push($parentId);

        $query->whereIn('parent_id', $sectionsIds);
    }


    public static function options(array $settings = [ ])
    {
        $sections = static::collectChildSections($settings);

        static::initProductsCountForSections($sections);

        return $sections->map(function ($section) {
            $pading = str_repeat('&nbsp;', 4 * $section->depth);
            $count  = ! empty( $section->productsCount ) ? ' (' . $section->productsCount . ')' : '';

            return $pading . $section->name . $count;
        });
    }


    public static function collectChildSections(array $settings = [ ], Collection $collection = null)
    {
        $settings = array_merge([
            'parent_id' => 0,
            'depth'     => 0,
            'except'    => [ ]
        ], $settings);

        if (is_null($collection)) {
            $collection = collect();
        }

        if ( ! is_array($settings['except'])) {
            $settings['except'] = [ $settings['except'] ];
        }

        foreach (Section::ordered()->byParent($settings['parent_id'])->get() as $section) {
            if (in_array($section->id, $settings['except'])) {
                continue;
            }

            $section->depth = $settings['depth'];

            $collection[$section->id] = $section;

            static::collectChildSections(array_merge($settings,
                [ 'parent_id' => $section->id, 'depth' => $settings['depth'] + 1 ]), $collection);
        }

        return $collection;
    }


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
    }
}