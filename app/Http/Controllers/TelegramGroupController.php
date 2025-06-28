<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramGroup;

class TelegramGroupController extends Controller
{
    public function index()
    {
        $groups = TelegramGroup::orderBy('created_at', 'desc')->paginate(15);
        
        return view('telegram-groups.index', compact('groups'));
    }

    public function show(TelegramGroup $telegramGroup)
    {
        $telegramGroup->load(['scheduledMessages']);
        
        return view('telegram-groups.show', compact('telegramGroup'));
    }

    public function edit(TelegramGroup $telegramGroup)
    {
        return view('telegram-groups.edit', compact('telegramGroup'));
    }

    public function update(Request $request, TelegramGroup $telegramGroup)
    {
        $request->validate([
            'is_active' => 'boolean'
        ]);

        $telegramGroup->update($request->only(['is_active']));

        return redirect()->route('telegram-groups.index')
                        ->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroy(TelegramGroup $telegramGroup)
    {
        $telegramGroup->delete();

        return redirect()->route('telegram-groups.index')
                        ->with('success', 'Grupo removido com sucesso!');
    }
}
