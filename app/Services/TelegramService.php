<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\ScheduledMessage;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    private $client;
    private $botToken;
    private $apiUrl;

    public function __construct()
    {
        $this->botToken = config('app.telegram_bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
    }

    /**
     * Enviar mensagem de texto
     */
    public function sendMessage($chatId, $text, $parseMode = 'HTML')
    {
        try {
            $response = $this->client->post($this->apiUrl . '/sendMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => $parseMode
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'message_id' => $result['result']['message_id'],
                    'data' => $result['result']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Erro desconhecido'
                ];
            }
        } catch (RequestException $e) {
            Log::error('Erro ao enviar mensagem Telegram: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar foto
     */
    public function sendPhoto($chatId, $photoPath, $caption = null)
    {
        try {
            $multipart = [
                [
                    'name' => 'chat_id',
                    'contents' => $chatId
                ],
                [
                    'name' => 'photo',
                    'contents' => fopen(Storage::path('public/' . $photoPath), 'r'),
                    'filename' => basename($photoPath)
                ]
            ];

            if ($caption) {
                $multipart[] = [
                    'name' => 'caption',
                    'contents' => $caption
                ];
                $multipart[] = [
                    'name' => 'parse_mode',
                    'contents' => 'HTML'
                ];
            }

            $response = $this->client->post($this->apiUrl . '/sendPhoto', [
                'multipart' => $multipart
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'message_id' => $result['result']['message_id'],
                    'data' => $result['result']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Erro desconhecido'
                ];
            }
        } catch (RequestException $e) {
            Log::error('Erro ao enviar foto Telegram: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar vídeo
     */
    public function sendVideo($chatId, $videoPath, $caption = null)
    {
        try {
            $multipart = [
                [
                    'name' => 'chat_id',
                    'contents' => $chatId
                ],
                [
                    'name' => 'video',
                    'contents' => fopen(Storage::path('public/' . $videoPath), 'r'),
                    'filename' => basename($videoPath)
                ]
            ];

            if ($caption) {
                $multipart[] = [
                    'name' => 'caption',
                    'contents' => $caption
                ];
                $multipart[] = [
                    'name' => 'parse_mode',
                    'contents' => 'HTML'
                ];
            }

            $response = $this->client->post($this->apiUrl . '/sendVideo', [
                'multipart' => $multipart
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'message_id' => $result['result']['message_id'],
                    'data' => $result['result']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Erro desconhecido'
                ];
            }
        } catch (RequestException $e) {
            Log::error('Erro ao enviar vídeo Telegram: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Configurar webhook
     */
    public function setWebhook($url)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/setWebhook', [
                'json' => [
                    'url' => $url
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => $result['ok'],
                'description' => $result['description'] ?? 'Webhook configurado'
            ];
        } catch (RequestException $e) {
            Log::error('Erro ao configurar webhook: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter informações do webhook
     */
    public function getWebhookInfo()
    {
        try {
            $response = $this->client->get($this->apiUrl . '/getWebhookInfo');
            $result = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => $result['ok'],
                'data' => $result['result'] ?? null
            ];
        } catch (RequestException $e) {
            Log::error('Erro ao obter info do webhook: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar mensagem agendada
     */
    public function sendScheduledMessage(ScheduledMessage $scheduledMessage)
    {
        $result = null;
        $status = 'failed';
        $errorMessage = null;
        $telegramMessageId = null;

        try {
            // Determinar o tipo de envio baseado na mídia
            if ($scheduledMessage->media_type && $scheduledMessage->media_path) {
                switch ($scheduledMessage->media_type) {
                    case 'photo':
                        $result = $this->sendPhoto(
                            $scheduledMessage->recipient_id,
                            $scheduledMessage->media_path,
                            $scheduledMessage->message
                        );
                        break;
                    case 'video':
                        $result = $this->sendVideo(
                            $scheduledMessage->recipient_id,
                            $scheduledMessage->media_path,
                            $scheduledMessage->message
                        );
                        break;
                    default:
                        // Para documentos, usar sendMessage por enquanto
                        $result = $this->sendMessage(
                            $scheduledMessage->recipient_id,
                            $scheduledMessage->message
                        );
                        break;
                }
            } else {
                // Enviar apenas texto
                $result = $this->sendMessage(
                    $scheduledMessage->recipient_id,
                    $scheduledMessage->message
                );
            }

            if ($result['success']) {
                $status = 'sent';
                $telegramMessageId = $result['message_id'];
            } else {
                $errorMessage = $result['error'];
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem agendada: ' . $e->getMessage());
            $errorMessage = $e->getMessage();
        }

        // Registrar log
        MessageLog::create([
            'scheduled_message_id' => $scheduledMessage->id,
            'recipient_id' => $scheduledMessage->recipient_id,
            'recipient_type' => $scheduledMessage->recipient_type,
            'message_content' => $scheduledMessage->message,
            'status' => $status,
            'error_message' => $errorMessage,
            'telegram_message_id' => $telegramMessageId,
            'sent_at' => $status === 'sent' ? now() : null
        ]);

        // Atualizar mensagem agendada
        if ($status === 'sent') {
            $scheduledMessage->last_sent_at = now();
            
            // Calcular próxima execução se for recorrente
            if ($scheduledMessage->recurrence_type !== 'none') {
                $scheduledMessage->next_run_at = $scheduledMessage->calculateNextRunAt();
            } else {
                $scheduledMessage->is_active = false; // Desativar mensagens únicas após envio
            }
            
            $scheduledMessage->save();
        }

        return [
            'success' => $status === 'sent',
            'status' => $status,
            'error' => $errorMessage,
            'telegram_message_id' => $telegramMessageId
        ];
    }
}

