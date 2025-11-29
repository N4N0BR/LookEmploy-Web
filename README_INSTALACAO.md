C:\xampp\php\php.exe C:\xampp\htdocs\LookEmploy\composer.phar dump-autoload
# 🚀 Guia Rápido de Instalação - Chat Seguro

## ✅ Pré-requisitos
- XAMPP instalado e rodando
- PHP 7.4 ou superior
- MySQL rodando
- Composer instalado

## 📦 Passo 1: Instalar Dependências

```bash
cd C:\xampp\htdocs\LookEmploy\api_chat
composer install
```

## 🗄️ Passo 2: Configurar Banco de Dados

Abra o phpMyAdmin e execute:

```sql
SOURCE C:/xampp/htdocs/LookEmploy/sql/security_tables.sql
```

Ou importe o arquivo manualmente.

## 🔧 Passo 3: Configurar Variáveis de Ambiente (Opcional mas Recomendado)

Crie um arquivo `.env` em `api_chat/`:

```env
JWT_SECRET=sua_chave_secreta_muito_forte_aqui_min_64_caracteres_recomendado
ENCRYPTION_KEY=outra_chave_secreta_para_criptografia_min_32_chars
```

## 🎯 Passo 4: Iniciar o Servidor

**Opção 1: Usando o .bat**
```
Duplo clique em: iniciar_chat_seguro.bat
```

**Opção 2: Manualmente**
```bash
cd C:\xampp\htdocs\LookEmploy\api_chat
php servidor_chat_seguro.php
```

## 🌐 Passo 5: Atualizar Frontend

Edite o arquivo `contatos.php` e substitua a linha do JavaScript:

**DE:**
```html
<script src="js/contatos.js"></script>
```

**PARA:**
```html
<script src="js/contatos_seguro.js"></script>
```

## ✔️ Passo 6: Testar

1. Abra o navegador
2. Acesse: `http://localhost/LookEmploy/contatos.php`
3. Verifique no console do servidor se aparece:
   ```
   ==========================================
     SERVIDOR DE CHAT SEGURO - LOOKEMPLOY
   ==========================================

   Sistema de segurança:
     [✓] Autenticação JWT
     [✓] Criptografia AES-256-GCM
     [✓] Rate Limiting
     [✓] Controle de Permissões
     [✓] Logs de Auditoria
   
   Servidor pronto! Aguardando conexões...
   ```

## 🔍 Verificar Instalação

Execute no MySQL:

```sql
-- Verificar se tabelas foram criadas
SHOW TABLES LIKE 'security%';
SHOW TABLES LIKE 'usuarios_bloqueados';

-- Verificar índices
SHOW INDEX FROM mensagens;
```

Você deve ver:
- `security_logs`
- `usuarios_bloqueados`
- Vários índices na tabela `mensagens`

## 🐛 Solução de Problemas

### Erro: "Composer not found"
```bash
# Instalar Composer
# Baixe de: https://getcomposer.org/download/
```

### Erro: "Port 8080 already in use"
```bash
# Encontrar o processo usando a porta
netstat -ano | findstr :8080

# Matar o processo (substitua PID pelo número encontrado)
taskkill /PID [PID] /F
```

### Erro: "Class not found"
```bash
# Reinstalar dependências
cd api_chat
composer dump-autoload
```

### Erro: "Cannot connect to database"
Verifique em `api_chat/conectar.php`:
```php
$pdo = new PDO(
    "mysql:host=localhost;dbname=lookemploy;charset=utf8mb4",
    "root",  // Seu usuário MySQL
    ""       // Sua senha MySQL
);
```

## 📊 Monitoramento

### Ver logs de segurança em tempo real:
```sql
SELECT * FROM security_logs 
ORDER BY timestamp DESC 
LIMIT 20;
```

### Ver estatísticas:
```sql
SELECT 
    DATE(timestamp) as data,
    event_type,
    COUNT(*) as total
FROM security_logs
GROUP BY DATE(timestamp), event_type
ORDER BY data DESC;
```

## 🔒 Segurança em Produção

### IMPORTANTE: Antes de colocar em produção:

1. **Gerar chaves fortes:**
   ```bash
   # No terminal PHP
   php -r "echo bin2hex(random_bytes(32));"
   ```

2. **Usar variáveis de ambiente** (nunca deixar chaves no código)

3. **Ativar WSS** (WebSocket Seguro com certificado SSL)

4. **Configurar firewall** para aceitar apenas conexões na porta 8080 de IPs confiáveis

5. **Backup automático** da tabela `security_logs`

## 📞 Suporte

Se encontrar problemas:

1. Verifique os logs em `api_chat/logs/security.log`
2. Verifique o console do servidor WebSocket
3. Verifique o console do navegador (F12)
4. Consulte a documentação completa no artifact

## 🎉 Pronto!

Seu sistema de chat agora está:
- ✅ Protegido com JWT
- ✅ Mensagens criptografadas
- ✅ Rate limiting ativo
- ✅ Logs de auditoria funcionando
- ✅ Controle de permissões implementado

---

**Versão:** 2.0 Seguro  
**Data:** Novembro 2024  
**Desenvolvido para:** LookEmploy
