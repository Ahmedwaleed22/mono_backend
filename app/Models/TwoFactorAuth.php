<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, string $string1, $id)
 */
class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = "2fa";
}
