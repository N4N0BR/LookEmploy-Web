<?php
include_once 'conexaoMySQL.php';
session_start();

$desc = $_POST['descricao'] ?? '';
$dataServico = $_POST['dataServico'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$logradouro = $_POST['logradouro'] ?? '';
$numero = $_POST['numero'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$metodo = $_POST['metodo'] ?? '';
$cliente = $_POST['cliente'];
$prestador = $_POST['prestador'] ?? '';

if (!isset($_SESSION['usuario'])) {
    header('location: login.html');
    exit();
} else {

    // CONVERTER PARA FORMATO MYSQL
    date_default_timezone_set('America/Sao_Paulo');
    $data = date("Y-m-d H:i", strtotime(str_replace('/', '-', $dataServico)));

    if (!empty($_POST['codigoServico'])) {
        if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'Cliente') {
            echo "Apenas clientes podem editar serviços.";
            exit();
        }

        $codigoServico = $_POST['codigoServico'];
        $stmtChk = $conexao->prepare("SELECT cliente FROM Servico WHERE codigoServico = ? LIMIT 1");
        $stmtChk->bind_param('s', $codigoServico);
        $stmtChk->execute();
        $resChk = $stmtChk->get_result();
        if (!$resChk || $resChk->num_rows === 0) {
            echo "Serviço não encontrado.";
            exit();
        }
        $rowChk = $resChk->fetch_assoc();
        if (strval($rowChk['cliente']) !== strval($cliente)) {
            echo "Permissão negada para editar este serviço.";
            exit();
        }
        $stmtChk->close();

        $sql_update = "UPDATE Servico SET bairro=?, logradouro=?, numero=?, complemento=?, dataServico=?, tipoPagamento=?, descricao=? WHERE codigoServico=?";
        $stmt = $conexao->prepare($sql_update);
        if (!$stmt) { echo "Erro ao preparar o SQL: " . $conexao->error; exit; }
        $stmt->bind_param(
            'ssssssss',
            $bairro,
            $logradouro,
            $numero,
            $complemento,
            $data,
            $metodo,
            $desc,
            $codigoServico
        );

        if ($stmt->execute()) {
            echo "EXITO";
            $stmt->close();
            $conexao->close();
            exit();
        } else {
            echo "Erro ao atualizar o serviço: " . $stmt->error;
            exit();
        }
    }

    $sql_insert = "INSERT INTO Servico 
        (bairro, logradouro, numero, complemento, dataServico, tipoPagamento, descricao, contrato, prestador, cliente) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', ?, ?)";

    $stmt = $conexao->prepare($sql_insert);

    if (!$stmt) {
        echo "Erro ao preparar o SQL: " . $conexao->error;
        exit;
    }

    $stmt->bind_param(
        'sssssssss',
        $bairro,
        $logradouro,
        $numero,
        $complemento,
        $data,
        $metodo,
        $desc,   // <- corrigido
        $prestador,
        $cliente
    );

    if ($stmt->execute()) {
        // Garantir vínculo com tabela usuarios para contatos do chat
        // Cliente
        $stmtCli = $conexao->prepare("SELECT usuario_id, nome, email, senha FROM Cliente WHERE ID = ?");
        $stmtCli->bind_param('s', $cliente);
        $stmtCli->execute();
        $resCli = $stmtCli->get_result();
        if ($resCli && ($rowCli = $resCli->fetch_assoc())) {
            if (empty($rowCli['usuario_id'])) {
                // Criar usuário de chat se não existir
                $stmtChkU = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND tipo = 'cliente' LIMIT 1");
                $stmtChkU->bind_param('s', $rowCli['email']);
                $stmtChkU->execute();
                $resU = $stmtChkU->get_result();
                if ($resU && $resU->num_rows > 0) {
                    $uid = $resU->fetch_assoc()['id'];
                } else {
                    $stmtInsU = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, tipo, online) VALUES (?, ?, ?, 'cliente', 0)");
                    $stmtInsU->bind_param('sss', $rowCli['nome'], $rowCli['email'], $rowCli['senha']);
                    $stmtInsU->execute();
                    $uid = $stmtInsU->insert_id;
                    $stmtInsU->close();
                }
                $stmtUpdCli = $conexao->prepare("UPDATE Cliente SET usuario_id = ? WHERE ID = ?");
                $stmtUpdCli->bind_param('is', $uid, $cliente);
                $stmtUpdCli->execute();
                $stmtUpdCli->close();
                $stmtChkU->close();
            }
        }
        $stmtCli->close();

        // Prestador
        $stmtPre = $conexao->prepare("SELECT usuario_id, nome, email, senha FROM Prestador WHERE ID = ?");
        $stmtPre->bind_param('s', $prestador);
        $stmtPre->execute();
        $resPre = $stmtPre->get_result();
        if ($resPre && ($rowPre = $resPre->fetch_assoc())) {
            if (empty($rowPre['usuario_id'])) {
                $stmtChkU2 = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND tipo = 'prestador' LIMIT 1");
                $stmtChkU2->bind_param('s', $rowPre['email']);
                $stmtChkU2->execute();
                $resU2 = $stmtChkU2->get_result();
                if ($resU2 && $resU2->num_rows > 0) {
                    $uid2 = $resU2->fetch_assoc()['id'];
                } else {
                    $stmtInsU2 = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, tipo, online) VALUES (?, ?, ?, 'prestador', 0)");
                    $stmtInsU2->bind_param('sss', $rowPre['nome'], $rowPre['email'], $rowPre['senha']);
                    $stmtInsU2->execute();
                    $uid2 = $stmtInsU2->insert_id;
                    $stmtInsU2->close();
                }
                $stmtUpdPre = $conexao->prepare("UPDATE Prestador SET usuario_id = ? WHERE ID = ?");
                $stmtUpdPre->bind_param('is', $uid2, $prestador);
                $stmtUpdPre->execute();
                $stmtUpdPre->close();
                $stmtChkU2->close();
            }
        }
        $stmtPre->close();

        echo "EXITO";
        $stmt->close();
        $conexao->close();
    } else {
        echo "Erro ao contratar o serviço: " . $stmt->error;
        exit();
    }
}
?>
