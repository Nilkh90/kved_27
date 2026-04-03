<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nace2027 extends Model
{
    protected $table = 'nace_2027';

    protected $fillable = [
        'code',
        'title',
        'level',
        'parent_id',
        'description',
        'includes',
        'excludes',
        'includes_also'
    ];

    protected $casts = [
        'includes' => 'array',
        'excludes' => 'array',
        'includes_also' => 'array',
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

    /**
     * Get the SEO-friendly slug for the code (dots replaced by hyphens).
     */
    public function getSlugAttribute(): string
    {
        return str_replace('.', '-', $this->code);
    }
}

