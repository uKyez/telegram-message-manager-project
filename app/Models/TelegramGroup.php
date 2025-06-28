<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'title',
        'type',
        'username',
        'description',
        'is_active'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_active' => 'boolean'
    ];

    public function scheduledMessages()
    {
        return $this->hasMany(ScheduledMessage::class, 'recipient_id', 'telegram_id')
                    ->where('recipient_type', 'group');
    }
}
