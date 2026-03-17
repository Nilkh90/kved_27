<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kved2010 extends Model
{
    protected $table = 'kved_2010';

    protected $fillable = [
        'code',
        'title',
        'level',
        'parent_id',
        'description',
        'includes',
        'excludes'
    ];

    protected $casts = [
        'includes' => 'array',
        'excludes' => 'array',
    ];

    /**
     * The parent element of this node.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The children elements nested under this node.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Scopes for specific hierarchy levels
    public function scopeSections($query)
    {
        return $query->where('level', 'SECTION');
    }

    public function scopeDivisions($query)
    {
        return $query->where('level', 'DIVISION');
    }

    public function scopeGroups($query)
    {
        return $query->where('level', 'GROUP');
    }

    public function scopeClasses($query)
    {
        return $query->where('level', 'CLASS');
    }
}

