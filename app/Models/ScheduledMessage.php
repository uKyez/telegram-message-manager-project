<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduledMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'media_type',
        'media_path',
        'recipient_type',
        'recipient_id',
        'scheduled_at',
        'recurrence_type',
        'recurrence_interval',
        'next_run_at',
        'last_sent_at',
        'is_active'
    ];

    protected $casts = [
        'recipient_id' => 'integer',
        'scheduled_at' => 'datetime',
        'next_run_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'recurrence_interval' => 'integer',
        'is_active' => 'boolean'
    ];

    public function messageLogs()
    {
        return $this->hasMany(MessageLog::class);
    }

    public function recipient()
    {
        if ($this->recipient_type === 'user') {
            return $this->belongsTo(TelegramUser::class, 'recipient_id', 'telegram_id');
        } else {
            return $this->belongsTo(TelegramGroup::class, 'recipient_id', 'telegram_id');
        }
    }

    public function calculateNextRunAt()
    {
        if ($this->recurrence_type === 'none') {
            return null;
        }

        $baseDate = $this->last_sent_at ?: $this->scheduled_at;
        
        switch ($this->recurrence_type) {
            case 'daily':
                return $baseDate->addDays($this->recurrence_interval);
            case 'weekly':
                return $baseDate->addWeeks($this->recurrence_interval);
            case 'monthly':
                return $baseDate->addMonths($this->recurrence_interval);
            case 'yearly':
                return $baseDate->addYears($this->recurrence_interval);
            default:
                return null;
        }
    }

    public function scopeDueForSending($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('next_run_at', '<=', now())
                          ->orWhere(function ($subQ) {
                              $subQ->whereNull('next_run_at')
                                   ->where('scheduled_at', '<=', now())
                                   ->whereNull('last_sent_at');
                          });
                    });
    }
}
