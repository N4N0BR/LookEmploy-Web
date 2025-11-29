-- ============================================================================
-- SCRIPT COMPLETO PARA CORRIGIR O SISTEMA DE USUÁRIOS E CHAT
-- ============================================================================

USE lookemploy;

-- Passo 1: Garantir que as colunas usuario_id existem
ALTER TABLE Cliente 
ADD COLUMN IF NOT EXISTS usuario_id INT UNIQUE;

ALTER TABLE Prestador 
ADD COLUMN IF NOT EXISTS usuario_id INT UNIQUE;

-- Passo 2: Inserir CLIENTES na tabela usuarios (evitando duplicatas)
INSERT INTO usuarios (nome, email, senha, tipo, online)
SELECT 
    CONCAT(c.nome, ' ', c.sobrenome) as nome,
    c.email,
    c.senha,
    'Cliente' as tipo,
    0 as online
FROM Cliente c
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios u 
    WHERE u.email = c.email
);

-- Passo 3: Atualizar Cliente.usuario_id baseado no email
UPDATE Cliente c
INNER JOIN usuarios u ON c.email = u.email
SET c.usuario_id = u.id
WHERE c.usuario_id IS NULL;

-- Passo 4: Inserir PRESTADORES na tabela usuarios (evitando duplicatas)
INSERT INTO usuarios (nome, email, senha, tipo, online)
SELECT 
    CONCAT(p.nome, ' ', p.sobrenome) as nome,
    p.email,
    p.senha,
    'Prestador' as tipo,
    0 as online
FROM Prestador p
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios u 
    WHERE u.email = p.email
);

-- Passo 5: Atualizar Prestador.usuario_id baseado no email
UPDATE Prestador p
INNER JOIN usuarios u ON p.email = u.email
SET p.usuario_id = u.id
WHERE p.usuario_id IS NULL;

-- ============================================================================
-- VERIFICAÇÃO: Mostrar resultado
-- ============================================================================

SELECT '=== RESUMO ===' as status;

SELECT 
    'Total de Clientes' as tabela,
    COUNT(*) as total,
    SUM(CASE WHEN usuario_id IS NOT NULL THEN 1 ELSE 0 END) as com_usuario_id,
    SUM(CASE WHEN usuario_id IS NULL THEN 1 ELSE 0 END) as sem_usuario_id
FROM Cliente

UNION ALL

SELECT 
    'Total de Prestadores',
    COUNT(*),
    SUM(CASE WHEN usuario_id IS NOT NULL THEN 1 ELSE 0 END),
    SUM(CASE WHEN usuario_id IS NULL THEN 1 ELSE 0 END)
FROM Prestador

UNION ALL

SELECT 
    'Total na tabela usuarios',
    COUNT(*),
    COUNT(*),
    0
FROM usuarios;

-- Mostrar alguns exemplos
SELECT '=== EXEMPLOS DE CLIENTES ===' as status;
SELECT 
    c.ID,
    c.nome,
    c.email,
    c.usuario_id,
    u.nome as nome_usuarios
FROM Cliente c
LEFT JOIN usuarios u ON c.usuario_id = u.id
LIMIT 5;

SELECT '=== EXEMPLOS DE PRESTADORES ===' as status;
SELECT 
    p.ID,
    p.nome,
    p.email,
    p.usuario_id,
    u.nome as nome_usuarios
FROM Prestador p
LEFT JOIN usuarios u ON p.usuario_id = u.id
LIMIT 5;

-- Verificar o serviço específico que você está testando
SELECT '=== SERVIÇO #1 ===' as status;
SELECT 
    s.codigoServico,
    s.prestador as prestador_id,
    p.nome as prestador_nome,
    p.usuario_id as prestador_usuario_id,
    s.cliente as cliente_id,
    c.nome as cliente_nome,
    c.usuario_id as cliente_usuario_id
FROM Servico s
LEFT JOIN Prestador p ON s.prestador = p.ID
LEFT JOIN Cliente c ON s.cliente = c.ID
WHERE s.codigoServico = 1;

SELECT 'Script concluído com sucesso!' as status;
