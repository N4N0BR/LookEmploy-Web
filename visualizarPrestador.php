<?php
    // Iniciar a sessão
    session_start();

    // Verificar se o usuário está logado
    if (!isset($_SESSION['usuario'])) {
        // Redirecionar para a página de login se não estiver logado
        header('location: login.html');
        exit();
    }
    else {
        //pegando o id da URL
        $id = $_GET['id'] ?? 1;

        if(is_numeric($id)) {
            //buscando os dados do prestador no banco de dados
            $conn = new mysqli('localhost', 'root', '', 'lookemploy');
            if ($conn->connect_error) {
                die("Erro de conexão: " . $conn->connect_error);
            }
            else {
                $stmt = $conn->prepare("SELECT nome, sobrenome, tipoServico, descricao, avaliacao, caminhoImagemPerfil, usuario_id FROM Prestador WHERE ID = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                if ($row) {
                    $nome = $row["nome"];
                    $sobrenome = $row["sobrenome"];
                    $servico =  $row["tipoServico"];
                    $descricao = $row["descricao"];
                    $avaliacao = $row["avaliacao"];
                    $caminhoImagemPerfil = "img/img_perfil/" . $row["caminhoImagemPerfil"];
                    $usuarioIdPrestador = $row["usuario_id"] ?? null;
                }
            }
        }
        else exit();
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisualizarPrestador</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="css/design_visualizarPrestador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="img/logo_icon.png">
</head>
<body>
    <!--Menu lateral-->
    <div id="inserirMenuLateral"></div>

    <!--PERFIL-->    
    <section class="perfil">
        <!--FOTO E OPÇÕES-->
        <section class="secao" style="background: linear-gradient(to top, white 45%, whitesmoke 45%);">
            <a target="_self" href="telaInicial.php"><button>Voltar</button></a>

            <div class="informacoes">
                <!--FOTO E NOME-->
                <div class="inicio">
                    <img class="fotoPerfil" src="<?= $caminhoImagemPerfil ?>" alt="Foto de perfil">
                    <div class="column">
                        <!--NOME E DESCRIÇÃO-->
                        <?php echo "<h1>". htmlspecialchars($nome). " " . htmlspecialchars($sobrenome). "</h1>"; ?>

                        <!--avaliação-->
                        <div class="avaliacao">
                            <?php
                                for($i = 0; $i < $avaliacao; $i++) {
                                    echo "<i class='fa-solid fa-star' style='color: #5CE1E6;'></i>";
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <!--BOTÕES-->
                <div class="row">
                    <?php
                        echo "<a href='contratarServico.php?id=" . $id . "'><button>Contratar</button></a>";
                        $openParam = $usuarioIdPrestador ? (int)$usuarioIdPrestador : '';
                        echo "<a target='_self' href='contatos.php" . ($openParam ? ("?open=".$openParam) : "") . "'><button>Conversar</button></a>";
                    ?>
                </div>
            </div>
        </section>

        <!--TIPO DE SERVIÇO-->
        <section class="secao">
            <?php
                if($servico != null) echo "<h3>". htmlspecialchars($servico)."</h3>";
            ?>
        </section>
        <hr class="line">
        
        <!--DESCRIÇÃO E NECESSIDADE-->
        <section class="secao">
            <div class="descricao">
                <h1>Descrição</h1>
                <?php
                    if($descricao != null) echo "<p>". htmlspecialchars($descricao)."</p>";
                ?>
            </div>
        </section>
    </section>
    <script src="js/menuLateral.js"></script>
</body>
</html>
