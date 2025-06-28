<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;
use App\Models\TelegramGroup;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            
            Log::info('Webhook recebido:', $update);

            // Verificar se é uma mensagem
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            // Verificar se é um novo membro em grupo
            if (isset($update['message']['new_chat_members'])) {
                $this->handleNewChatMembers($update['message']);
            }

            // Verificar se o bot foi adicionado a um grupo
            if (isset($update['my_chat_member'])) {
                $this->handleMyChatMember($update['my_chat_member']);
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Erro no webhook: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function handleMessage($message)
    {
        // Salvar/atualizar usuário
        if (isset($message['from'])) {
            $this->saveOrUpdateUser($message['from']);
        }

        // Salvar/atualizar grupo se a mensagem for de um grupo
        if (isset($message['chat']) && in_array($message['chat']['type'], ['group', 'supergroup', 'channel'])) {
            $this->saveOrUpdateGroup($message['chat']);
        }
    }

    private function handleNewChatMembers($message)
    {
        foreach ($message['new_chat_members'] as $member) {
            $this->saveOrUpdateUser($member);
        }
    }

    private function handleMyChatMember($myChatMember)
    {
        $chat = $myChatMember['chat'];
        
        if (in_array($chat['type'], ['group', 'supergroup', 'channel'])) {
            $this->saveOrUpdateGroup($chat);
        }
    }

    private function saveOrUpdateUser($userData)
    {
        try {
            TelegramUser::updateOrCreate(
                ['telegram_id' => $userData['id']],
                [
                    'first_name' => $userData['first_name'] ?? '',
                    'last_name' => $userData['last_name'] ?? null,
                    'username' => $userData['username'] ?? null,
                    'language_code' => $userData['language_code'] ?? null,
                    'is_bot' => $userData['is_bot'] ?? false,
                    'is_active' => true
                ]
            );

            Log::info('Usuário salvo/atualizado: ' . $userData['id']);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar usuário: ' . $e->getMessage());
        }
    }

    private function saveOrUpdateGroup($chatData)
    {
        try {
            TelegramGroup::updateOrCreate(
                ['telegram_id' => $chatData['id']],
                [
                    'title' => $chatData['title'] ?? '',
                    'type' => $chatData['type'] ?? 'group',
                    'username' => $chatData['username'] ?? null,
                    'description' => $chatData['description'] ?? null,
                    'is_active' => true
                ]
            );

            Log::info('Grupo salvo/atualizado: ' . $chatData['id']);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar grupo: ' . $e->getMessage());
        }
    }
}
