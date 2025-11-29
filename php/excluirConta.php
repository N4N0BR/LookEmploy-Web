<?php

include_once 'conexaoMySQL.php';
session_start();

// Verificar login
if (!isset($_SESSION['usuario'])) {
    header('location: ../login.html');
    exit();
}

$tipo = $_SESSION['tipo'];
$ID = $_SESSION['usuario'];

$sql = "DELETE FROM $tipo WHERE ID = ?";
$stmt = $conexao->prepare($sql);

if (!$stmt) {
    echo "<script>alert('Erro ao preparar comando SQL!');</script>";
    exit;
}

$stmt->bind_param("i", $ID);

// Execução
if ($stmt->execute()) {
    echo "<script>
            alert('Conta deletada.');
            window.location.replace('../login.html');
          </script>";
    exit;
} else {
    echo "<script>
            alert('Erro ao deletar! Tente novamente.');
            window.location.replace('../login.html');
          </script>";
    exit;
}

$stmt->close();
$conexao->close();
?>
