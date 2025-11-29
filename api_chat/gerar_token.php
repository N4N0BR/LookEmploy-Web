<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Autoload robusto: tenta autoload local e, se não existir, o da raiz do projeto
$autoloads = [
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php'
];
foreach ($autoloads as $autoload) {
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

use Api\Security\JWTHandler;
use Dotenv\Dotenv;

if (class_exists(Dotenv::class)) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    // Fallback: carregar .env manualmente
    $envFile = __DIR__ . '/.env';
    if (is_file($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1], " \"\'" );
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
                putenv("$key=$val");
            }
        }
    }
}

// Verificar se usuário está logado
// Aceitar chaves alternativas na sessão para compatibilidade
$userId = isset($_SESSION['usuario']) ? (int)$_SESSION['usuario'] : (isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0);
$userName = $_SESSION['nome'] ?? ($_SESSION['user_name'] ?? '');
$userType = $_SESSION['tipo'] ?? ($_SESSION['user_type'] ?? '');

if (!$userId || !$userName || !$userType) {
    echo json_encode([
        'error' => 'Usuário não autenticado',
        'details' => [
            'usuario' => $_SESSION['usuario'] ?? null,
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
            'nome' => $_SESSION['nome'] ?? null,
            'tipo' => $_SESSION['tipo'] ?? null
        ]
    ]);
    exit();
}

try {
    $jwtHandler = new JWTHandler();
    
    // Gerar token com os dados da sessão
    $token = $jwtHandler->generateToken(
        $userId,        // ID do usuário
        $userName,      // Nome do usuário
        $userType       // Tipo (Cliente/Prestador)
    );

    echo json_encode([
        'success' => true,
        'token' => $token,
        'usuario' => [
            'id' => $userId,
            'nome' => $userName,
            'tipo' => $userType
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erro ao gerar token',
        'details' => $e->getMessage()
    ]);
}
