<?php
  session_start();
  include_once 'conexaoMySQL.php';

  $tipo = $_POST['tipo'] ?? '';
  $email = $_POST['email'] ?? '';
  $senha = $_POST['senha'] ?? '';
  $nome = $_POST['nome'] ?? '';
  $sobrenome = $_POST['sobrenome'] ?? '';
  $telefone = $_POST['telefone'] ?? '';
  $data_nascimento = $_POST['dataNascimento'] ?? '';
  $bairro = $_POST['bairro'] ?? '';
  $logradouro = $_POST['logradouro'] ?? '';
  $numero = $_POST['numero'] ?? '';
  $complemento = $_POST['complemento'] ?? '';
  $sexo = $_POST['sexo'] ?? '';
  if($tipo == "Prestador") { $servico = $_POST['servico'] ?? ''; }

  function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
  }

  $tipo = test_input($tipo);
  $email = test_input($email);
  $senha = test_input($senha);
  $nome = test_input($nome);
  $sobrenome = test_input($sobrenome);
  $telefone = test_input($telefone);
  $data_nascimento = test_input($data_nascimento);
  $bairro = test_input($bairro);
  $logradouro = test_input($logradouro);
  $numero = test_input($numero);
  $complemento = test_input($complemento);
  $sexo = test_input($sexo);

  $erros = [];
  if (empty($tipo)) $erros[] = 'tipo';
  if (empty($nome)) $erros[] = 'nome';
  if (empty($sobrenome)) $erros[] = 'sobrenome';
  if (empty($email)) $erros[] = 'email';
  if (empty($senha)) $erros[] = 'senha';
  if (empty($telefone)) $erros[] = 'telefone';
  if (empty($data_nascimento)) $erros[] = 'dataNascimento';
  if (empty($bairro)) $erros[] = 'bairro';
  if (empty($logradouro)) $erros[] = 'logradouro';
  if (empty($numero)) $erros[] = 'numero';
  if (empty($sexo)) $erros[] = 'sexo';
  if ($tipo === 'Prestador' && (empty($servico))) $erros[] = 'servico';

  if (!empty($erros)) {
    echo 'Erro: dados incompletos (' . implode(', ', $erros) . ').';
    exit;
  }

  $allowed_tables = ['Cliente', 'Prestador'];
  if (!in_array($tipo, $allowed_tables, true)) {
    echo'Tipo de conta inválido.';
    exit;
  }

  // Normalizar telefone (apenas dígitos)
  $telefone = preg_replace('/\D+/', '', $telefone);
  $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

  $sql_check = "SELECT email FROM {$tipo} WHERE email = ? LIMIT 1";
  $stmt = $conexao->prepare($sql_check);
  if ($stmt === false) {
    echo"Erro no servidor.";
    exit;
  }
  $stmt->bind_param('s', $email);
  if (!$stmt->execute()) {
    echo'Erro ao verificar email: ' . $stmt->error;
    $stmt->close();
    $conexao->close();
    exit;
  }
  $result = $stmt->get_result();
  if ($result && $result->num_rows > 0) {
  echo "Uma conta com este e-mail já existe. Utilize outro e-mail.";
    $stmt->close();
    $conexao->close();
    exit;
  }
  $stmt->close();

  switch($sexo) {
    case "Masculino":
    $caminhoImagemPerfil = "img_icone_masculino.png";
    break;
    case "Feminino":
    $caminhoImagemPerfil = "img_icone_feminino.png";
    break;
    default:
    $caminhoImagemPerfil = "img_icone_neutro.png";
  }

  switch ($tipo) {
    case 'Prestador':
      if ($complemento === '') {
        $sql_insert = "INSERT INTO {$tipo} (nome, sobrenome, email, senha, telefone, dataNascimento, bairro, logradouro, numero, sexo, tipoServico, caminhoImagemPerfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql_insert);
        if ($stmt === false) {
          echo'Erro no servidor (prepare insert).';
          $conexao->close();
          exit;
        }
        $stmt->bind_param('ssssssssssss', $nome, $sobrenome, $email, $senhaHash, $telefone, $data_nascimento, $bairro, $logradouro, $numero, $sexo, $servico, $caminhoImagemPerfil);
      } else {
        $sql_insert = "INSERT INTO {$tipo} (nome, sobrenome, email, senha, telefone, dataNascimento, bairro, logradouro, numero, complemento, sexo, tipoServico, caminhoImagemPerfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql_insert);
        if ($stmt === false) {
          echo'Erro no servidor (prepare insert).';
          $conexao->close();
          exit;
        }
        $stmt->bind_param('sssssssssssss', $nome, $sobrenome, $email, $senhaHash, $telefone, $data_nascimento, $bairro, $logradouro, $numero, $complemento, $sexo, $servico, $caminhoImagemPerfil);
      }
      break;
    default:
      if ($complemento === '') {
        $sql_insert = "INSERT INTO {$tipo} (nome, sobrenome, email, senha, telefone, dataNascimento, bairro, logradouro, numero, sexo, caminhoImagemPerfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql_insert);
        if ($stmt === false) {
          echo'Erro no servidor (prepare insert).';
          $conexao->close();
          exit;
        }
        $stmt->bind_param('sssssssssss', $nome, $sobrenome, $email, $senhaHash, $telefone, $data_nascimento, $bairro, $logradouro, $numero, $sexo, $caminhoImagemPerfil);
      } else {
        $sql_insert = "INSERT INTO {$tipo} (nome, sobrenome, email, senha, telefone, dataNascimento, bairro, logradouro, numero, complemento, sexo, caminhoImagemPerfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql_insert);
        if ($stmt === false) {
          echo'Erro no servidor (prepare insert).';
          $conexao->close();
          exit;
        }
        $stmt->bind_param('ssssssssssss', $nome, $sobrenome, $email, $senhaHash, $telefone, $data_nascimento, $bairro, $logradouro, $numero, $complemento, $sexo, $caminhoImagemPerfil);
      }
      break;
  }
  if ($stmt->execute()) {
      $stmt->close();
      $sql = "SELECT * FROM $tipo WHERE email = ?";
      $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        echo "Erro ao logar" . $conexao->error;
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

            $_SESSION['tipo'] = $tipo;
            $_SESSION['usuario'] = $usuario['ID'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['sobrenome'] = $usuario['sobrenome'];
            $_SESSION['dataNascimento'] = $usuario['dataNascimento'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['telefone'] = $usuario['telefone'];
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
            $stmt->close();
            $conexao->close();

      } else {
        echo'Erro ao cadastrar! Tente novamente.';
        error_log('DB insert error: ' . $stmt->error);

        $stmt->close();
        $conexao->close();
        exit;
      }
  }
?>
