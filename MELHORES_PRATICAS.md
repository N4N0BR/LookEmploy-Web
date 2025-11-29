# 🔒 Melhores Práticas de Segurança - LookEmploy Chat

## 📋 Checklist de Segurança

### Antes de Ir para Produção

- [ ] **Chaves Secretas**
  - [ ] Gerar nova chave JWT (mínimo 64 caracteres)
  - [ ] Gerar nova chave de criptografia (mínimo 32 caracteres)
  - [ ] Mover chaves para variáveis de ambiente
  - [ ] Nunca commitar chaves no Git

- [ ] **SSL/TLS**
  - [ ] Obter certificado SSL (Let's Encrypt é grátis)
  - [ ] Configurar WSS (WebSocket Seguro)
  - [ ] Redirecionar HTTP para HTTPS
  - [ ] Configurar HSTS

- [ ] **Banco de Dados**
  - [ ] Criar usuário MySQL específico (não usar root)
  - [ ] Dar apenas permissões necessárias
  - [ ] Ativar SSL na conexão MySQL
  - [ ] Configurar backup automático

- [ ] **Servidor**
  - [ ] Configurar firewall
  - [ ] Desabilitar listagem de diretórios
  - [ ] Ocultar versão do PHP
  - [ ] Configurar rate limiting no nível do servidor

- [ ] **Monitoramento**
  - [ ] Configurar alertas de segurança
  - [ ] Monitorar logs regularmente
  - [ ] Configurar rotação de logs
  - [ ] Implementar sistema de backup dos logs

---

## 🔐 Configuração de Variáveis de Ambiente

### Método 1: Usando .env (Desenvolvimento)

Crie o arquivo `.env` em `api_chat/`:

```env
# JWT Configuration
JWT_SECRET=sua_chave_jwt_super_secreta_aqui_min_64_caracteres_necessarios_12345678
JWT_EXPIRATION=86400

# Encryption Configuration
ENCRYPTION_KEY=sua_chave_criptografia_min_32_chars_aqui

# Database Configuration
DB_HOST=localhost
DB_NAME=lookemploy
DB_USER=lookemploy_user
DB_PASS=senha_forte_aqui

# WebSocket Configuration
WS_HOST=localhost
WS_PORT=8080

# Environment
APP_ENV=production
APP_DEBUG=false
```

Adicione ao `.gitignore`:
```
.env
.env.local
```

### Método 2: Variáveis de Sistema (Produção)

**Windows:**
```batch
setx JWT_SECRET "sua_chave_jwt_aqui" /M
setx ENCRYPTION_KEY "sua_chave_criptografia_aqui" /M
```

**Linux:**
```bash
export JWT_SECRET="sua_chave_jwt_aqui"
export ENCRYPTION_KEY="sua_chave_criptografia_aqui"

# Adicionar ao /etc/environment para persistir
```

---

## 🛡️ Configuração WSS (WebSocket Seguro)

### Passo 1: Obter Certificado SSL

```bash
# Usando Let's Encrypt (gratuito)
sudo apt-get install certbot
sudo certbot certonly --standalone -d seudominio.com
```

### Passo 2: Configurar WebSocket com SSL

Crie `servidor_chat_wss.php`:

```php
<?php
use Api\WebSocket\SistemaChatSeguro;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SistemaChatSeguro()
        )
    ),
    8080,
    '0.0.0.0',
    [
        'tls' => [
            'local_cert' => '/etc/letsencrypt/live/seudominio.com/fullchain.pem',
            'local_pk' => '/etc/letsencrypt/live/seudominio.com/privkey.pem',
            'verify_peer' => false
        ]
    ]
);

$server->run();
```

### Passo 3: Atualizar Frontend

```javascript
// Mudar de ws:// para wss://
const ws = new WebSocket("wss://seudominio.com:8080");
```

---

## 🔥 Firewall e Regras de Rede

### Linux (UFW)

```bash
# Permitir apenas porta 8080 de IPs específicos
sudo ufw allow from SEU_IP_FRONTEND to any port 8080

# Ou permitir de qualquer lugar (menos seguro)
sudo ufw allow 8080/tcp

# Ativar firewall
sudo ufw enable
```

### Windows Firewall

```powershell
# PowerShell como Administrador
New-NetFirewallRule -DisplayName "WebSocket Chat" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
```

---

## 📊 Monitoramento e Alertas

### Script de Monitoramento (PHP)

Crie `api_chat/monitor.php`:

```php
<?php
require_once __DIR__ . '/conectar.php';

// Verificar eventos críticos nas últimas 24h
$stmt = $pdo->query("
    SELECT COUNT(*) as count, severity, event_type
    FROM security_logs
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
      AND severity IN ('ERROR', 'CRITICAL')
    GROUP BY severity, event_type
");

$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($alerts as $alert) {
    if ($alert['count'] > 10) {
        // Enviar email de alerta
        mail(
            'admin@seudominio.com',
            "Alerta de Segurança: {$alert['event_type']}",
            "Detectados {$alert['count']} eventos do tipo {$alert['event_type']} com severidade {$alert['severity']}"
        );
    }
}
```

### Agendar Monitoramento (Cron)

```bash
# Executar a cada hora
0 * * * * /usr/bin/php /path/to/api_chat/monitor.php
```

---

## 🗄️ Backup Automático

### Script de Backup

Crie `backup.sh`:

```bash
#!/bin/bash

# Configurações
DB_NAME="lookemploy"
BACKUP_DIR="/backups/lookemploy"
DATE=$(date +%Y%m%d_%H%M%S)

# Criar diretório se não existir
mkdir -p $BACKUP_DIR

# Backup do banco
mysqldump -u root -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Backup dos logs
tar -czf $BACKUP_DIR/logs_$DATE.tar.gz api_chat/logs/

# Remover backups antigos (manter últimos 7 dias)
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup concluído: $DATE"
```

Agendar (Cron):
```bash
# Todo dia às 3h da manhã
0 3 * * * /path/to/backup.sh
```

---

## 🔍 Auditoria de Segurança Regular

### Queries Úteis

**Tentativas de autenticação falhadas:**
```sql
SELECT 
    user_id,
    ip_address,
    COUNT(*) as tentativas,
    MAX(timestamp) as ultima_tentativa
FROM security_logs
WHERE event_type = 'FAILED_AUTH'
  AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY user_id, ip_address
HAVING tentativas > 5;
```

**Usuários mais ativos:**
```sql
SELECT 
    u.nome,
    COUNT(m.id) as total_mensagens,
    DATE(m.data_envio) as data
FROM mensagens m
JOIN usuarios u ON m.remetente_id = u.id
WHERE m.data_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY u.nome, DATE(m.data_envio)
ORDER BY total_mensagens DESC;
```

**Atividades suspeitas:**
```sql
SELECT *
FROM security_logs
WHERE severity IN ('ERROR', 'CRITICAL')
  AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY timestamp DESC;
```

---

## 🚨 Resposta a Incidentes

### Se Detectar Atividade Suspeita:

1. **Identificar o usuário:**
   ```sql
   SELECT * FROM security_logs 
   WHERE user_id = [ID_SUSPEITO]
   ORDER BY timestamp DESC;
   ```

2. **Bloquear temporariamente:**
   ```sql
   UPDATE usuarios SET online = 0 WHERE id = [ID_SUSPEITO];
   ```

3. **Revisar mensagens:**
   ```sql
   SELECT * FROM mensagens 
   WHERE remetente_id = [ID_SUSPEITO] OR destinatario_id = [ID_SUSPEITO]
   ORDER BY data_envio DESC;
   ```

4. **Documentar:**
   - Salvar logs
   - Fazer backup das evidências
   - Registrar ações tomadas

5. **Notificar:**
   - Usuários afetados
   - Equipe de segurança
   - Autoridades (se necessário)

---

## 📈 Métricas de Performance

### Monitorar:

- **Taxa de mensagens/segundo**
- **Latência do WebSocket**
- **Uso de memória**
- **Uso de CPU**
- **Taxa de erro**
- **Número de conexões simultâneas**

### Tools Recomendadas:

- **New Relic** - Monitoramento APM
- **Datadog** - Logs e métricas
- **Grafana** - Visualização
- **Prometheus** - Coleta de métricas

---

## ✅ Teste de Penetração

### Testes Recomendados:

1. **SQL Injection**
   - Testar todos os inputs
   - Verificar prepared statements

2. **XSS (Cross-Site Scripting)**
   - Testar campos de mensagem
   - Verificar escape de HTML

3. **CSRF (Cross-Site Request Forgery)**
   - Implementar tokens CSRF
   - Validar origem das requisições

4. **Rate Limiting**
   - Testar envio massivo de mensagens
   - Verificar bloqueio

5. **Autenticação**
   - Testar tokens expirados
   - Testar tokens falsificados
   - Testar múltiplas sessões

---

## 📚 Recursos Adicionais

### Documentação:

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP WebSocket Security](https://owasp.org/www-community/vulnerabilities/WebSocket_Protocol)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

### Ferramentas de Teste:

- **OWASP ZAP** - Scanner de vulnerabilidades
- **Burp Suite** - Testes de penetração
- **SQLMap** - Teste de SQL Injection
- **Wireshark** - Análise de tráfego

---

## 🎓 Treinamento da Equipe

### Tópicos Importantes:

1. **Segurança básica**
   - Senhas fortes
   - Autenticação de dois fatores
   - Phishing

2. **Código seguro**
   - Input validation
   - Output encoding
   - Prepared statements

3. **Resposta a incidentes**
   - Como identificar
   - Como responder
   - Como documentar

---

## 📞 Contatos de Emergência

```
Equipe de Segurança: security@seudominio.com
Suporte Técnico: suporte@seudominio.com
Emergência (24h): +55 11 9999-9999
```

---

**Última Atualização:** Novembro 2024  
**Responsável:** Equipe de Desenvolvimento LookEmploy  
**Revisão:** Mensal
