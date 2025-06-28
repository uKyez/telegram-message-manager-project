<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledMessage;
use App\Services\TelegramService;
use Carbon\Carbon;

class SendScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar mensagens agendadas do Telegram';

    private $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando processamento de mensagens agendadas...');

        // Buscar mensagens que devem ser enviadas
        $messages = ScheduledMessage::dueForSending()->get();

        if ($messages->isEmpty()) {
            $this->info('Nenhuma mensagem para enviar no momento.');
            return 0;
        }

        $this->info("Encontradas {$messages->count()} mensagem(s) para enviar.");

        $sent = 0;
        $failed = 0;

        foreach ($messages as $message) {
            $this->line("Processando: {$message->title}");

            try {
                $result = $this->telegramService->sendScheduledMessage($message);

                if ($result['success']) {
                    $this->info("✓ Enviada com sucesso (ID: {$result['telegram_message_id']})");
                    $sent++;
                } else {
                    $this->error("✗ Falha no envio: {$result['error']}");
                    $failed++;
                }

                // Pequena pausa entre envios para respeitar rate limits
                sleep(1);

            } catch (\Exception $e) {
                $this->error("✗ Erro inesperado: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Processamento concluído:");
        $this->info("- Enviadas: {$sent}");
        $this->info("- Falharam: {$failed}");

        return 0;
    }
}
