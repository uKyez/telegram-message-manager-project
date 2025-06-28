<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledMessage;
use App\Models\TelegramUser;
use App\Models\TelegramGroup;
use Carbon\Carbon;

class ScheduledMessageController extends Controller
{
    public function index()
    {
        $messages = ScheduledMessage::with(['messageLogs'])
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(15);
        
        return view('scheduled-messages.index', compact('messages'));
    }

    public function create()
    {
        $users = TelegramUser::where('is_active', true)->get();
        $groups = TelegramGroup::where('is_active', true)->get();
        
        return view('scheduled-messages.create', compact('users', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:user,group',
            'recipient_id' => 'required|integer',
            'scheduled_at' => 'required|date|after:now',
            'recurrence_type' => 'required|in:none,daily,weekly,monthly,yearly',
            'recurrence_interval' => 'required|integer|min:1',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200'
        ]);

        $data = $request->all();
        
        // Upload de mídia se fornecida
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('telegram-media', $filename, 'public');
            
            $data['media_path'] = $path;
            $data['media_type'] = $this->getMediaType($file->getClientMimeType());
        }

        // Calcular próxima execução se for recorrente
        if ($data['recurrence_type'] !== 'none') {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
            $data['next_run_at'] = $scheduledAt;
        }

        ScheduledMessage::create($data);

        return redirect()->route('scheduled-messages.index')
                        ->with('success', 'Mensagem agendada criada com sucesso!');
    }

    public function show(ScheduledMessage $scheduledMessage)
    {
        $scheduledMessage->load(['messageLogs.recipient']);
        
        return view('scheduled-messages.show', compact('scheduledMessage'));
    }

    public function edit(ScheduledMessage $scheduledMessage)
    {
        $users = TelegramUser::where('is_active', true)->get();
        $groups = TelegramGroup::where('is_active', true)->get();
        
        return view('scheduled-messages.edit', compact('scheduledMessage', 'users', 'groups'));
    }

    public function update(Request $request, ScheduledMessage $scheduledMessage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:user,group',
            'recipient_id' => 'required|integer',
            'scheduled_at' => 'required|date',
            'recurrence_type' => 'required|in:none,daily,weekly,monthly,yearly',
            'recurrence_interval' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200'
        ]);

        $data = $request->all();
        
        // Upload de nova mídia se fornecida
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('telegram-media', $filename, 'public');
            
            $data['media_path'] = $path;
            $data['media_type'] = $this->getMediaType($file->getClientMimeType());
        }

        // Recalcular próxima execução se necessário
        if ($data['recurrence_type'] !== 'none' && !$scheduledMessage->last_sent_at) {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
            $data['next_run_at'] = $scheduledAt;
        }

        $scheduledMessage->update($data);

        return redirect()->route('scheduled-messages.index')
                        ->with('success', 'Mensagem agendada atualizada com sucesso!');
    }

    public function destroy(ScheduledMessage $scheduledMessage)
    {
        $scheduledMessage->delete();

        return redirect()->route('scheduled-messages.index')
                        ->with('success', 'Mensagem agendada removida com sucesso!');
    }

    private function getMediaType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'photo';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'document';
        }
    }
}
