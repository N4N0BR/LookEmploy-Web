<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'Não autenticado']);
    exit();
}

if (!isset($_POST['codigoServico']) || !is_numeric($_POST['codigoServico'])) {
    echo json_encode(['error' => 'Código de serviço inválido']);
    exit();
}

$codigoServico = (int)$_POST['codigoServico'];
$meuId = (int)$_SESSION['usuario'];

try {
    require_once __DIR__ . '/conectar.php';
    
    // Buscar serviço atual
    $stmt = $pdo->prepare("
        SELECT contrato, prestador, cliente
        FROM Servico
        WHERE codigoServico = ?
    ");
    
    $stmt->execute([$codigoServico]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        echo json_encode(['error' => 'Serviço não encontrado']);
        exit();
    }
    
    $statusAtual = $servico['contrato'];
    
    // Determinar novo status baseado no atual
    $novoStatus = 'andamento';
    
    if ($statusAtual === 'pendente') {
        $novoStatus = 'andamento';
    } else if ($statusAtual === 'andamento') {
        $novoStatus = 'concluido';
    } else if ($statusAtual === 'concluido') {
        echo json_encode(['error' => 'Serviço já está concluído']);
        exit();
    }
    
    // Atualizar status
    $stmt = $pdo->prepare("
        UPDATE Servico
        SET contrato = ?
        WHERE codigoServico = ?
    ");
    
    $stmt->execute([$novoStatus, $codigoServico]);
    
    echo json_encode([
        'ok' => true,
        'contrato' => $novoStatus,
        'message' => 'Status atualizado para: ' . $novoStatus
    ]);
    
} catch (Exception $e) {
    error_log("Erro em aceitar_contrato.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao processar: ' . $e->getMessage()]);
}
