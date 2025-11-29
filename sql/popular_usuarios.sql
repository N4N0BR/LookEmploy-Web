-- Script para popular a tabela usuarios a partir de Cliente e Prestador
USE lookemploy;

-- Inserir clientes na tabela usuarios (se ainda não existirem)
INSERT INTO usuarios (nome, email, senha, tipo, online)
SELECT 
    CONCAT(c.nome, ' ', c.sobrenome) as nome,
    c.email,
    c.senha,
    'cliente' as tipo,
    0 as online
FROM Cliente c
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios u 
    WHERE u.email = c.email
);

-- Atualizar Cliente com usuario_id
UPDATE Cliente c
JOIN usuarios u ON c.email = u.email
SET c.usuario_id = u.id
WHERE c.usuario_id IS NULL;

-- Inserir prestadores na tabela usuarios (se ainda não existirem)
INSERT INTO usuarios (nome, email, senha, tipo, online)
SELECT 
    CONCAT(p.nome, ' ', p.sobrenome) as nome,
    p.email,
    p.senha,
    'prestador' as tipo,
    0 as online
FROM Prestador p
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios u 
    WHERE u.email = p.email
);

-- Atualizar Prestador com usuario_id
UPDATE Prestador p
JOIN usuarios u ON p.email = u.email
SET p.usuario_id = u.id
WHERE p.usuario_id IS NULL;

-- Verificar resultado
SELECT 
    'Clientes' as tabela,
    COUNT(*) as total
FROM Cliente
UNION ALL
SELECT 
    'Prestadores',
    COUNT(*)
FROM Prestador
UNION ALL
SELECT 
    'Usuários',
    COUNT(*)
FROM usuarios;

-- Mostrar usuários criados
SELECT 
    id,
    nome,
    email,
    tipo,
    online
FROM usuarios
ORDER BY tipo, nome;

-- Criar contratos de teste na tabela Servico (um por cliente sem contrato)
INSERT INTO Servico (
    bairro, logradouro, numero, complemento,
    dataServico, tipoPagamento, descricao, contrato,
    prestador, cliente
)
SELECT 
    c.bairro,
    c.logradouro,
    c.numero,
    c.complemento,
    DATE_ADD(NOW(), INTERVAL 1 DAY),
    'pix',
    'Contrato de teste',
    'pendente',
    (SELECT MIN(p.ID) FROM Prestador p),
    c.ID
FROM Cliente c
WHERE NOT EXISTS (
    SELECT 1 FROM Servico s WHERE s.cliente = c.ID
);

-- Resumo de contratos criados
SELECT COUNT(*) AS contratos_criados FROM Servico;
