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

try {
    require_once __DIR__ . '/conectar.php';
    
    // Buscar serviço atual
    $stmt = $pdo->prepare("
        SELECT contrato, dataServico
        FROM Servico
        WHERE codigoServico = ?
    ");
    
    $stmt->execute([$codigoServico]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        echo json_encode(['error' => 'Serviço não encontrado']);
        exit();
    }
    
    // Verificar elegibilidade para reembolso (24h antes)
    $dataServico = new DateTime($servico['dataServico']);
    $agora = new DateTime();
    $diferencaHoras = ($dataServico->getTimestamp() - $agora->getTimestamp()) / 3600;
    
    $reembolsoElegivel = $diferencaHoras > 24;
    
    // Atualizar status para cancelado
    $stmt = $pdo->prepare("
        UPDATE Servico
        SET contrato = 'cancelado'
        WHERE codigoServico = ?
    ");
    
    $stmt->execute([$codigoServico]);
    
    echo json_encode([
        'ok' => true,
        'reembolsoElegivel' => $reembolsoElegivel,
        'message' => 'Serviço cancelado' . ($reembolsoElegivel ? ' - Reembolso elegível' : '')
    ]);
    
} catch (Exception $e) {
    error_log("Erro em cancelar_contrato.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao processar: ' . $e->getMessage()]);
}
