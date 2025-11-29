<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=lookemploy;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '-03:00'");
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>
