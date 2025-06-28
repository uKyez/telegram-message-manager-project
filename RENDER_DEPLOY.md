# Guia de Deploy no Render.com - Sistema de Mensagens Telegram

Este guia fornece instruções passo a passo para fazer o deploy do sistema de gerenciamento e agendamento de mensagens Telegram no Render.com.

## 📋 Pré-requisitos

### 1. Conta no Render.com
- Crie uma conta gratuita em [render.com](https://render.com)
- Conecte sua conta GitHub/GitLab (recomendado)

### 2. Bot Telegram Configurado
- Token do bot obtido via @BotFather
- Username do bot anotado

### 3. Repositório Git
- Código do projeto em um repositório Git (GitHub, GitLab, etc.)
- Arquivo `render.yaml` na raiz do projeto (incluído)

## 🚀 Método 1: Deploy via Blueprint (Recomendado)

### Passo 1: Preparar o Repositório
1. Faça upload do código para seu repositório Git
2. Certifique-se de que o arquivo `render.yaml` está na raiz
3. Commit e push das alterações

### Passo 2: Deploy via Blueprint
1. Acesse [render.com](https://render.com) e faça login
2. Clique em "New" → "Blueprint"
3. Conecte seu repositório Git
4. Selecione o repositório do projeto
5. O Render detectará automaticamente o arquivo `render.yaml`
6. Clique em "Apply" para iniciar o deploy

### Passo 3: Configurar Variáveis de Ambiente
Durante o deploy, configure as seguintes variáveis:

```env
APP_NAME=Telegram Message Manager
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-app.onrender.com

# Será gerada automaticamente
APP_KEY=base64:...

# PostgreSQL (configurado automaticamente)
DATABASE_URL=postgresql://...

# Telegram Bot
TELEGRAM_BOT_TOKEN=seu_token_aqui
TELEGRAM_BOT_USERNAME=@seubotname_bot

# Configurações de produção
LOG_CHANNEL=stack
LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

## 🔧 Método 2: Deploy Manual

### Passo 1: Criar Web Service
1. No dashboard do Render, clique em "New" → "Web Service"
2. Conecte seu repositório Git
3. Configure:
   - **Name:** `telegram-message-manager`
   - **Environment:** `Docker` ou `Node`
   - **Build Command:** `composer install --optimize-autoloader --no-dev && npm install && npm run build`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`

### Passo 2: Criar PostgreSQL Database
1. Clique em "New" → "PostgreSQL"
2. Configure:
   - **Name:** `telegram-db`
   - **Plan:** Free (ou pago conforme necessidade)
3. Anote a URL de conexão fornecida

### Passo 3: Criar Cron Job
1. Clique em "New" → "Cron Job"
2. Configure:
   - **Name:** `telegram-scheduler`
   - **Command:** `php artisan telegram:send-scheduled`
   - **Schedule:** `* * * * *` (a cada minuto)

## 📝 Configuração do arquivo render.yaml

O arquivo `render.yaml` já está incluído no projeto com a seguinte configuração:

```yaml
services:
  # Web Service (Laravel App)
  - type: web
    name: telegram-message-manager
    env: php
    buildCommand: |
      composer install --optimize-autoloader --no-dev
      php artisan key:generate --force
      php artisan config:cache
      php artisan route:cache
      php artisan view:cache
      php artisan migrate --force
      php artisan storage:link
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_NAME
        value: Telegram Message Manager
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: LOG_CHANNEL
        value: stack
      - key: LOG_LEVEL
        value: error
      - key: DB_CONNECTION
        value: pgsql
      - key: SESSION_DRIVER
        value: database
      - key: CACHE_DRIVER
        value: database
      - key: QUEUE_CONNECTION
        value: database
      - key: TELEGRAM_BOT_TOKEN
        sync: false
      - key: TELEGRAM_BOT_USERNAME
        sync: false

  # Cron Job (Scheduler)
  - type: cron
    name: telegram-scheduler
    env: php
    schedule: "* * * * *"
    buildCommand: composer install --optimize-autoloader --no-dev
    startCommand: php artisan telegram:send-scheduled
    envVars:
      - key: APP_ENV
        value: production
      - key: DB_CONNECTION
        value: pgsql
      - key: TELEGRAM_BOT_TOKEN
        sync: false

databases:
  # PostgreSQL Database
  - name: telegram-db
    plan: free
```

## 🔐 Configuração de Variáveis de Ambiente

### Variáveis Obrigatórias
```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_BOT_USERNAME=@seubotname_bot
```

### Variáveis Opcionais
```env
APP_NAME=Telegram Message Manager
APP_DEBUG=false
LOG_LEVEL=error
```

### Variáveis Automáticas
O Render configurará automaticamente:
- `APP_KEY` (gerada durante o build)
- `DATABASE_URL` (conexão PostgreSQL)
- `APP_URL` (URL do seu app)
- `PORT` (porta do serviço)

## 🗄️ Configuração do Banco de Dados

### PostgreSQL no Render
1. O banco PostgreSQL será criado automaticamente
2. As migrações serão executadas durante o build
3. A URL de conexão será configurada automaticamente

### Estrutura das Tabelas
O sistema criará automaticamente:
- `users` - Usuários do painel administrativo
- `telegram_users` - Usuários do Telegram
- `telegram_groups` - Grupos do Telegram
- `scheduled_messages` - Mensagens agendadas
- `message_logs` - Logs de envio

## 🔄 Processo de Deploy

### Build Process
1. **Install Dependencies:** `composer install --optimize-autoloader --no-dev`
2. **Generate App Key:** `php artisan key:generate --force`
3. **Cache Configuration:** `php artisan config:cache`
4. **Cache Routes:** `php artisan route:cache`
5. **Cache Views:** `php artisan view:cache`
6. **Run Migrations:** `php artisan migrate --force`
7. **Link Storage:** `php artisan storage:link`

### Start Process
- **Web Service:** `php artisan serve --host=0.0.0.0 --port=$PORT`
- **Cron Job:** `php artisan telegram:send-scheduled` (executado a cada minuto)

## 🌐 Configuração do Webhook

### Após o Deploy
1. Anote a URL do seu app: `https://seu-app.onrender.com`
2. Configure o webhook do Telegram:

```bash
curl -X POST "https://api.telegram.org/botSEU_TOKEN/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url":"https://seu-app.onrender.com/telegram/webhook"}'
```

### Verificar Webhook
```bash
curl "https://api.telegram.org/botSEU_TOKEN/getWebhookInfo"
```

## 👤 Criar Usuário Administrador

### Via Console do Render
1. Acesse o dashboard do Render
2. Vá para seu web service
3. Clique em "Shell" para abrir o console
4. Execute:

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrador',
    'email' => 'admin@exemplo.com',
    'password' => Hash::make('sua_senha_segura'),
    'email_verified_at' => now()
]);

exit
```

## 📊 Monitoramento e Logs

### Logs do Render
- **Build Logs:** Visíveis durante o processo de deploy
- **Runtime Logs:** Acessíveis via dashboard do Render
- **Cron Logs:** Logs específicos do cron job

### Logs da Aplicação
- Laravel logs são enviados para stdout/stderr
- Visíveis no dashboard do Render
- Configurados para nível "error" em produção

## 🔧 Manutenção e Atualizações

### Deploy de Atualizações
1. Faça commit das alterações no repositório
2. Push para a branch principal
3. O Render fará redeploy automaticamente

### Comandos Úteis via Shell
```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Executar migrações
php artisan migrate

# Verificar status
php artisan telegram:send-scheduled
```

## 🚨 Solução de Problemas

### Deploy Falha
- Verifique os build logs no dashboard
- Certifique-se de que todas as dependências estão no `composer.json`
- Verifique se o arquivo `render.yaml` está correto

### Webhook não Funciona
- Verifique se a URL está acessível: `curl https://seu-app.onrender.com`
- Teste o endpoint: `curl -X POST https://seu-app.onrender.com/telegram/webhook`
- Verifique logs do web service

### Cron Job não Executa
- Verifique logs do cron job no dashboard
- Teste manualmente via shell: `php artisan telegram:send-scheduled`
- Verifique se as variáveis de ambiente estão configuradas

### Banco de Dados
- Verifique se a conexão PostgreSQL está funcionando
- Execute migrações manualmente se necessário: `php artisan migrate`
- Verifique logs para erros de SQL

## 💰 Custos no Render

### Plano Gratuito
- **Web Service:** 750 horas/mês (suficiente para uso contínuo)
- **PostgreSQL:** 1GB de armazenamento
- **Cron Job:** Incluído no plano gratuito
- **Limitações:** App hiberna após 15 min de inatividade

### Planos Pagos
- **Starter ($7/mês):** Sem hibernação, mais recursos
- **Standard ($25/mês):** Recursos adicionais, backups automáticos
- **Pro ($85/mês):** Alta disponibilidade, recursos premium

## 🔒 Segurança

### Configurações de Segurança
- HTTPS habilitado automaticamente
- Variáveis de ambiente criptografadas
- Acesso restrito ao shell e logs

### Boas Práticas
- Use senhas fortes para usuários admin
- Mantenha o token do bot seguro
- Configure `APP_DEBUG=false` em produção
- Use `LOG_LEVEL=error` para reduzir logs

## 📞 Suporte

### Recursos do Render
- [Documentação Oficial](https://render.com/docs)
- [Community Forum](https://community.render.com)
- [Status Page](https://status.render.com)

### Logs para Diagnóstico
- Build logs no dashboard
- Runtime logs do web service
- Logs do cron job
- Shell access para debugging

---

**Deploy realizado com sucesso! 🚀**

Acesse seu painel em `https://seu-app.onrender.com` e comece a usar o sistema de mensagens Telegram.

