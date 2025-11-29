<?php
  include_once 'conexaoMySQL.php';

  $tipo = $_POST['tipo'] ?? '';
  $senha = $_POST['senha'] ?? '';
  $email = $_POST['email'] ?? '';

  // Validação básica
  if ($senha != '' && $email != '') {
    
    //Comando de busca do email
    $sql = "SELECT * FROM $tipo WHERE email = ?";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {//EM CASO DE ERRO NO BANCO DE DADOS
        echo "Erro ao preparar o SQL: " . $conexao->error;
        exit;
    }

    // Associa o parâmetro (s = string) e executa a operação
    $stmt->bind_param("s", $email);
    $stmt->execute();

    //pega o resultado
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Verifica a senha (criptografada)
        if (password_verify($senha, $usuario['senha']) == true) {//SENHA CORRETA
            //pode iniciar uma sessão contendo as informações do usuario. Por exemplo:
            session_start();
            $_SESSION['tipo'] = $tipo;
            $_SESSION['usuario'] = $usuario['ID'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['sobrenome'] = $usuario['sobrenome'];
            $_SESSION['dataNascimento'] = $usuario['dataNascimento'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['telefone'] = $usuario['telefone'];
            $_SESSION['descricao'] = $usuario['descricao'];
            $_SESSION['bairro'] = $usuario['bairro'];
            $_SESSION['logradouro'] = $usuario['logradouro'];
            $_SESSION['numero'] = $usuario['numero'];
            $_SESSION['complemento'] = $usuario['complemento'];
            $_SESSION['sexo'] = $usuario['sexo'];
            $_SESSION['descricao'] = $usuario['descricao'];
            if($tipo == 'Prestador'){
              $_SESSION['tipoServico'] = $usuario['tipoServico']; 
              $_SESSION['avaliacao'] = $usuario['avaliacao'];
            }
            $_SESSION['caminhoImagemPerfil'] = $usuario['caminhoImagemPerfil'];

            echo "EXITO";
        }
        else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);//SENHA INCORRETA
            echo "Senha incorreta";        }
    } 
    else {
        echo "Usuário não encontrado.";
    }

    $stmt->close();
    $conexao->close();
  }
  else {
    echo("Erro: dados incompletos.");
    exit;
  }
?>
