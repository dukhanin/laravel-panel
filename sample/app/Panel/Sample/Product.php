<?php

namespace App\Panel\Sample;

use Dukhanin\Support\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Product extends EloquentModel
{

    protected $table = 'panel_sample_products';

    use HasSettings;

    protected $casts = [
        'settings' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'settings',
        'important',
        'enabled'
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

        $sectionsIds = Section::collectChildSections([ 'parent_id' => $sectionId ])->pluck('id')->push($sectionId);

        $query->whereIn('section_id', $sectionsIds);
    }
}