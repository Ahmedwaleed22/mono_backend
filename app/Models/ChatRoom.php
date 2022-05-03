<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $roomID)
 */
class ChatRoom extends Model
{
    use HasFactory;

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function serviceRequest() {
        return $this->belongsTo(ServiceRequest::class);
    }
}
