# Sistema de Gerenciamento e Agendamento de Mensagens Telegram

Um sistema completo desenvolvido em PHP (Laravel) para gerenciar e agendar o envio de mensagens via Telegram, com painel administrativo web e bot integrado.

## 🚀 Funcionalidades

### Painel Administrativo Web
- **Dashboard** com estatísticas em tempo real
- **Gerenciamento de Mensagens Agendadas** (criar, editar, visualizar, excluir)
- **Listagem de Usuários** do Telegram que interagiram com o bot
- **Listagem de Grupos** onde o bot foi adicionado
- **Logs de Envio** detalhados com status e erros
- **Sistema de Autenticação** seguro com Laravel Breeze

### Bot Telegram
- **Webhook** para receber atualizações em tempo real
- **Coleta Automática** de usuários e grupos
- **Envio de Mensagens** de texto, fotos e vídeos
- **Agendamento Flexível** com suporte a recorrência
- **Rate Limiting** para respeitar limites da API do Telegram

### Agendamento de Mensagens
- **Mensagens Únicas** ou **Recorrentes** (diária, semanal, mensal, anual)
- **Suporte a Mídia** (fotos, vídeos, documentos)
- **Múltiplos Destinatários** (usuários individuais ou grupos)
- **Execução via Cron Job** para hospedagem convencional
- **Logs Detalhados** de todos os envios

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 8.1+, Laravel 10.x
- **Frontend:** Blade Templates, Tailwind CSS, JavaScript
- **Banco de Dados:** SQLite (padrão) ou MySQL/PostgreSQL
- **API:** Telegram Bot API via Guzzle HTTP
- **Autenticação:** Laravel Breeze
- **Agendamento:** Cron Jobs + Artisan Commands

## 📋 Requisitos do Sistema

### Servidor Web
- PHP 8.1 ou superior
- Apache 2.4+ ou Nginx 1.18+
- Composer (gerenciador de dependências)
- Extensões PHP: cli, curl, mbstring, xml, zip, sqlite3, gd, bcmath, tokenizer, fileinfo

### Banco de Dados
- SQLite 3.x (recomendado)
- MySQL 5.7+ ou PostgreSQL 12+ (opcional)

### Outros
- Certificado SSL (para webhooks)
- Acesso a cron jobs
- Bot do Telegram configurado

## 🚀 Instalação Rápida

### 1. Preparar Bot Telegram
```bash
# No Telegram, procure @BotFather e execute:
/newbot
# Anote o token fornecido
```

### 2. Configurar Projeto
```bash
# Extrair arquivos e configurar permissões
chmod -R 755 storage/ bootstrap/cache/

# Configurar ambiente
cp .env.example .env
# Editar .env com suas configurações

# Gerar chave da aplicação
php artisan key:generate

# Executar migrações
php artisan migrate

# Criar link do storage
php artisan storage:link
```

### 3. Criar Usuário Admin
```bash
php artisan tinker
```
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrador',
    'email' => 'admin@exemplo.com',
    'password' => Hash::make('sua_senha'),
    'email_verified_at' => now()
]);
```

### 4. Configurar Webhook
```bash
curl -X POST "https://api.telegram.org/botSEU_TOKEN/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url":"https://seudominio.com/telegram/webhook"}'
```

### 5. Configurar Cron Job
```bash
# Adicionar ao crontab:
* * * * * /usr/bin/php /caminho/para/projeto/public/scheduler.php >> /dev/null 2>&1
```

## 📖 Guia de Uso

### Acessar o Painel
1. Acesse `https://seudominio.com`
2. Faça login com suas credenciais
3. Use o dashboard para navegar pelas funcionalidades

### Criar Mensagem Agendada
1. Vá em "Mensagens Agendadas" → "Nova Mensagem"
2. Preencha título, mensagem e destinatário
3. Configure data/hora e recorrência
4. Adicione mídia se necessário
5. Salve a mensagem

### Gerenciar Usuários e Grupos
- Os usuários são adicionados automaticamente quando interagem com o bot
- Os grupos são adicionados quando o bot é incluído neles
- Use as seções "Usuários" e "Grupos" para visualizar e gerenciar

### Monitorar Envios
- Acesse "Logs" para ver histórico de envios
- Verifique status (enviado/falhou) e mensagens de erro
- Use filtros para encontrar logs específicos

## 🔧 Configuração Avançada

### Variáveis de Ambiente (.env)
```env
# Aplicação
APP_NAME="Telegram Message Manager"
APP_ENV=production
APP_URL=https://seudominio.com

# Banco de Dados
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/para/database.sqlite

# Telegram Bot
TELEGRAM_BOT_TOKEN=seu_token_aqui
TELEGRAM_BOT_USERNAME=@seubotname_bot
```

### Otimização para Produção
```bash
# Cache de configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoloader
composer install --optimize-autoloader --no-dev
```

## 🐛 Solução de Problemas

### Erro 500 - Internal Server Error
- Verifique permissões: `chmod -R 755 storage/ bootstrap/cache/`
- Verifique logs: `tail -f storage/logs/laravel.log`

### Webhook não funciona
- Certifique-se de que o SSL está configurado
- Teste a URL: `curl https://seudominio.com/telegram/webhook`
- Verifique configuração do webhook: `https://api.telegram.org/botTOKEN/getWebhookInfo`

### Mensagens não são enviadas
- Verifique se o cron job está executando: `crontab -l`
- Teste manualmente: `php public/scheduler.php`
- Verifique logs: `tail -f storage/logs/scheduler.log`

### Bot não responde
- Verifique se o token está correto no `.env`
- Teste a API: `https://api.telegram.org/botTOKEN/getMe`
- Verifique se o bot não está bloqueado

## 📁 Estrutura do Projeto

```
telegram-message-manager/
├── app/
│   ├── Console/Commands/          # Comandos Artisan
│   ├── Http/Controllers/          # Controladores
│   ├── Models/                    # Modelos Eloquent
│   └── Services/                  # Serviços (TelegramService)
├── database/
│   ├── migrations/                # Migrações do banco
│   └── database.sqlite           # Banco SQLite
├── public/
│   ├── scheduler.php             # Script para cron job
│   └── storage/                  # Link simbólico
├── resources/views/              # Templates Blade
├── routes/web.php               # Rotas da aplicação
├── storage/logs/                # Logs da aplicação
├── .env                         # Configurações
├── INSTALLATION.md              # Guia de instalação detalhado
└── README.md                    # Este arquivo
```

## 🔒 Segurança

- **Autenticação** obrigatória para acesso ao painel
- **Validação** de dados em todos os formulários
- **Rate Limiting** para respeitar limites da API
- **Logs** detalhados para auditoria
- **Webhook** protegido contra acesso não autorizado

## 📝 Logs e Monitoramento

### Tipos de Log
- **Laravel:** `storage/logs/laravel.log`
- **Scheduler:** `storage/logs/scheduler.log`
- **Servidor Web:** logs do Apache/Nginx

### Monitoramento
- Dashboard com estatísticas em tempo real
- Logs de envio com status detalhado
- Alertas de erro nos logs da aplicação

## 🚀 Deploy no Render.com

Para deploy no Render.com, consulte o arquivo `RENDER_DEPLOY.md` incluído no projeto.

## 📞 Suporte

### Documentação
- [Laravel Documentation](https://laravel.com/docs)
- [Telegram Bot API](https://core.telegram.org/bots/api)

### Logs para Diagnóstico
```bash
# Logs da aplicação
tail -f storage/logs/laravel.log

# Logs do scheduler
tail -f storage/logs/scheduler.log

# Testar comando manualmente
php artisan telegram:send-scheduled
```

## 📄 Licença

Este projeto é fornecido como está, para uso educacional e comercial.

## 🤝 Contribuição

Para melhorias e correções:
1. Faça um fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Abra um Pull Request

---

**Desenvolvido com ❤️ usando Laravel e Telegram Bot API**

