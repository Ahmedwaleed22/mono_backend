<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $serviceSlug)
 */
class SubService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'search_tags',
        'min_price',
        'max_price',
        'currency',
        'slug'
    ];

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
