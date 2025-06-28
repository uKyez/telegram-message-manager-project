<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_bot',
        'is_active'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_bot' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function scheduledMessages()
    {
        return $this->hasMany(ScheduledMessage::class, 'recipient_id', 'telegram_id')
                    ->where('recipient_type', 'user');
    }
}
