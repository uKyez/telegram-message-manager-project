<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MessageLog;

class MessageLogController extends Controller
{
    public function index()
    {
        $logs = MessageLog::with(['scheduledMessage', 'recipient'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
        
        return view('message-logs.index', compact('logs'));
    }

    public function show(MessageLog $messageLog)
    {
        $messageLog->load(['scheduledMessage', 'recipient']);
        
        return view('message-logs.show', compact('messageLog'));
    }
}
