<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;

class TelegramUserController extends Controller
{
    public function index()
    {
        $users = TelegramUser::orderBy('created_at', 'desc')->paginate(15);
        
        return view('telegram-users.index', compact('users'));
    }

    public function show(TelegramUser $telegramUser)
    {
        $telegramUser->load(['scheduledMessages']);
        
        return view('telegram-users.show', compact('telegramUser'));
    }

    public function edit(TelegramUser $telegramUser)
    {
        return view('telegram-users.edit', compact('telegramUser'));
    }

    public function update(Request $request, TelegramUser $telegramUser)
    {
        $request->validate([
            'is_active' => 'boolean'
        ]);

        $telegramUser->update($request->only(['is_active']));

        return redirect()->route('telegram-users.index')
                        ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(TelegramUser $telegramUser)
    {
        $telegramUser->delete();

        return redirect()->route('telegram-users.index')
                        ->with('success', 'Usuário removido com sucesso!');
    }
}
