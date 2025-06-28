<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_message_id',
        'recipient_id',
        'recipient_type',
        'message_content',
        'status',
        'error_message',
        'telegram_message_id',
        'sent_at'
    ];

    protected $casts = [
        'scheduled_message_id' => 'integer',
        'recipient_id' => 'integer',
        'telegram_message_id' => 'integer',
        'sent_at' => 'datetime'
    ];

    public function scheduledMessage()
    {
        return $this->belongsTo(ScheduledMessage::class);
    }

    public function recipient()
    {
        if ($this->recipient_type === 'user') {
            return $this->belongsTo(TelegramUser::class, 'recipient_id', 'telegram_id');
        } else {
            return $this->belongsTo(TelegramGroup::class, 'recipient_id', 'telegram_id');
        }
    }
}
