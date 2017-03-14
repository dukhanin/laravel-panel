<?php

namespace App\Sample;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Product extends EloquentModel
{

    protected $table = 'sample_products';

    protected $casts = [
        'settings' => 'array',
        'images'   => 'array'
    ];

    protected $fillable = [
        'name',
        'images',
        'description',
        'settings',
        'important',
        'enabled',
        'section_id'
    ];


    public function scopeOrdered($query)
    {
        $query->orderBy('index', 'asc');
    }


    public function scopeBySection($query, $sectionId = 0)
    {
        if ($sectionId instanceof Section) {
            $sectionId = $sectionId->getKey();
        }

        $query->where('section_id', intval($sectionId));
    }


    public function scopeBySectionRecursive($query, $sectionId = 0)
    {
        if ($sectionId instanceof Section) {
            $sectionId = $sectionId->getKey();
        }

        $sectionsIds = Section::find($sectionId)->nested()->get([ 'id' ])->pluck('id')->push($sectionId);

        $query->whereIn('section_id', $sectionsIds);
    }
}