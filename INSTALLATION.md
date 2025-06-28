# Guia de Instalação - Sistema de Mensagens Telegram

Este guia fornece instruções passo a passo para instalar o sistema de gerenciamento e agendamento de mensagens Telegram em hospedagem convencional.

## Requisitos do Sistema

### Servidor Web
- Apache 2.4+ ou Nginx 1.18+
- PHP 8.1 ou superior
- Extensões PHP necessárias:
  - php-cli
  - php-common
  - php-curl
  - php-mbstring
  - php-xml
  - php-zip
  - php-sqlite3 (ou php-mysql se usar MySQL)
  - php-gd
  - php-bcmath
  - php-tokenizer
  - php-fileinfo

### Banco de Dados
- SQLite 3.x (recomendado para simplicidade)
- OU MySQL 5.7+ / MariaDB 10.3+

### Outros
- Composer (gerenciador de dependências PHP)
- Acesso a cron jobs
- Certificado SSL (recomendado para webhooks)

## Passo 1: Preparação do Bot Telegram

### 1.1 Criar o Bot
1. Abra o Telegram e procure por `@BotFather`
2. Envie `/newbot` e siga as instruções
3. Anote o **token do bot** fornecido (formato: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)
4. Anote o **username do bot** (formato: `@seubotname_bot`)

### 1.2 Configurar o Bot (Opcional)
```
/setdescription - Definir descrição do bot
/setabouttext - Definir texto "sobre"
/setuserpic - Definir foto do perfil
```

## Passo 2: Upload dos Arquivos

### 2.1 Fazer Upload
1. Extraia o arquivo `telegram-message-manager-source.zip`
2. Faça upload de todos os arquivos para o diretório público do seu servidor (geralmente `public_html` ou `www`)
3. Certifique-se de que a estrutura de pastas foi preservada

### 2.2 Configurar Permissões
Execute os seguintes comandos via SSH ou painel de controle:

```bash
# Definir permissões para diretórios de escrita
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/storage/

# Se necessário, ajustar proprietário
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

## Passo 3: Configuração do Ambiente

### 3.1 Configurar Arquivo .env
1. Renomeie `.env.example` para `.env`
2. Edite o arquivo `.env` com suas configurações:

```env
APP_NAME="Telegram Message Manager"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seudominio.com

# Banco de Dados SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/completo/para/database/database.sqlite

# OU para MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=telegram_manager
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha

# Configurações do Bot Telegram
TELEGRAM_BOT_TOKEN=SEU_TOKEN_AQUI
TELEGRAM_BOT_USERNAME=@seubotname_bot
```

### 3.2 Gerar Chave da Aplicação
Execute via SSH:
```bash
php artisan key:generate
```

## Passo 4: Configuração do Banco de Dados

### 4.1 Para SQLite
```bash
# Criar arquivo do banco
touch database/database.sqlite
chmod 664 database/database.sqlite

# Executar migrações
php artisan migrate
```

### 4.2 Para MySQL
1. Crie um banco de dados MySQL
2. Configure as credenciais no `.env`
3. Execute as migrações:
```bash
php artisan migrate
```

## Passo 5: Configuração do Servidor Web

### 5.1 Apache (.htaccess)
Certifique-se de que o arquivo `.htaccess` está presente na pasta `public/`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 5.2 Nginx
Configuração do virtual host:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name seudominio.com;
    root /caminho/para/projeto/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Passo 6: Criar Usuário Administrador

Execute via SSH:
```bash
php artisan tinker
```

No console do Tinker:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrador',
    'email' => 'admin@seudominio.com',
    'password' => Hash::make('sua_senha_segura'),
    'email_verified_at' => now()
]);

exit
```

## Passo 7: Configurar Webhook do Telegram

### 7.1 Via Navegador
Acesse: `https://api.telegram.org/botSEU_TOKEN/setWebhook?url=https://seudominio.com/telegram/webhook`

### 7.2 Via cURL
```bash
curl -X POST "https://api.telegram.org/botSEU_TOKEN/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url":"https://seudominio.com/telegram/webhook"}'
```

### 7.3 Verificar Webhook
Acesse: `https://api.telegram.org/botSEU_TOKEN/getWebhookInfo`

## Passo 8: Configurar Cron Job

### 8.1 Adicionar ao Crontab
Execute `crontab -e` e adicione:

```bash
# Executar a cada minuto
* * * * * /usr/bin/php /caminho/completo/para/projeto/public/scheduler.php >> /dev/null 2>&1
```

### 8.2 Verificar Caminho do PHP
```bash
which php
```

### 8.3 Testar Manualmente
```bash
php /caminho/para/projeto/public/scheduler.php
```

## Passo 9: Configurações Finais

### 9.1 Otimizar para Produção
```bash
# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Otimizar autoloader
composer install --optimize-autoloader --no-dev
```

### 9.2 Configurar Storage Link
```bash
php artisan storage:link
```

## Passo 10: Teste do Sistema

### 10.1 Acessar o Painel
1. Acesse `https://seudominio.com`
2. Faça login com as credenciais do administrador
3. Verifique se o dashboard carrega corretamente

### 10.2 Testar Bot
1. Procure seu bot no Telegram
2. Envie `/start` para o bot
3. Verifique se o usuário aparece no painel (seção "Usuários")

### 10.3 Testar Agendamento
1. Crie uma mensagem agendada para alguns minutos no futuro
2. Aguarde o cron job executar
3. Verifique se a mensagem foi enviada

## Solução de Problemas

### Erro 500 - Internal Server Error
- Verifique permissões das pastas `storage/` e `bootstrap/cache/`
- Verifique logs em `storage/logs/laravel.log`
- Certifique-se de que todas as extensões PHP estão instaladas

### Webhook não funciona
- Verifique se o SSL está configurado corretamente
- Teste a URL do webhook diretamente no navegador
- Verifique logs do servidor web

### Cron job não executa
- Verifique se o caminho do PHP está correto
- Teste o script manualmente
- Verifique logs em `storage/logs/scheduler.log`

### Mensagens não são enviadas
- Verifique se o token do bot está correto
- Verifique se o bot tem permissão para enviar mensagens
- Verifique logs em `storage/logs/laravel.log`

## Suporte

Para suporte adicional, verifique:
- Logs da aplicação em `storage/logs/`
- Logs do servidor web
- Documentação oficial do Laravel: https://laravel.com/docs

