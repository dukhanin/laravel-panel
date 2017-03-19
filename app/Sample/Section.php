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


    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id', 'id');
    }


    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }


    public function scopeOrderedDefault($query)
    {
        $query->orderBy('index', 'asc');
    }
}