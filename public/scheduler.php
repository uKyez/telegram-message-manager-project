<?php

/**
 * Script para execução via cron job em hospedagens convencionais
 * 
 * Este script deve ser executado a cada minuto via cron job:
 * * * * * * /usr/bin/php /caminho/para/o/projeto/public/scheduler.php >> /dev/null 2>&1
 */

// Definir o diretório base do projeto
$basePath = dirname(__DIR__);

// Carregar o autoloader do Composer
require_once $basePath . '/vendor/autoload.php';

// Carregar o framework Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Inicializar o kernel do console
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Executar o comando de envio de mensagens agendadas
$status = $kernel->call('telegram:send-scheduled');

// Log do resultado (opcional)
$logFile = $basePath . '/storage/logs/scheduler.log';
$timestamp = date('Y-m-d H:i:s');
$message = "[{$timestamp}] Comando telegram:send-scheduled executado com status: {$status}\n";

// Criar diretório de logs se não existir
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Escrever log
file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);

// Retornar status
exit($status);

