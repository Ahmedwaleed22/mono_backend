<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, bool $true)
 * @method static orderBy(string $string, string $string1)
 * @method static pluck(string $string)
 */
class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'slug',
        'is_primary'
    ];

    public function subServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubService::class);
    }
}
