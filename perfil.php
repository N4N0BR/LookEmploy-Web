<?php
    // Iniciar a sessão
    session_start();

    // Verificar se o usuário está logado
    if (!isset($_SESSION['usuario'])) {
        // Redirecionar para a página de login se não estiver logado
        header('location: login.html');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="css/design_perfil.css">
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
            <div class="informacoes">
                <!--FOTO E NOME-->
                <div class="inicio">
                    <img class="fotoPerfil" src="
                    <?php 
                        echo "img/img_perfil/" . rawurlencode($_SESSION['caminhoImagemPerfil']);
                    ?>" alt="Foto de perfil">
                    <div class="column">
                        <h1><?= htmlspecialchars(($_SESSION["nome"] ?? "") . " " . ($_SESSION["sobrenome"] ?? "")) ?></h1>
 
                            <div class='row'>
                                <?php
                                if($_SESSION['tipo'] == 'Prestador'){
                                    if($_SESSION['avaliacao'] == null) {
                                        echo "Ainda não há avaliações suas.";
                                    } else {
                                        for($i = 0; $i < $_SESSION['avaliacao']; $i++) {
                                        echo"<i class='fa-solid fa-star' style='color: #5CE1E6;'></i>";
                                        }
                                    }
                                }
                                ?>
                            </div>
                    </div>
                </div>

                <!--BOTÕES-->

                <div class="row">
                    <button style="display: <?php if($_SESSION['tipo'] == "Prestador") { 
                        echo "block";
                     } else {
                        echo "none";
                     }
                      ?>">Avaliações</button>
                    <a href="editarPerfil.php"><button>Editar perfil</button></a>
                    <a href="php/realizarLogout.php"><button>Sair da conta</button></a>
                </div>
            </div>
        </section>

        <!--TIPO DE SERVIÇO-->
        <section class="secao">
            <?php
            if($_SESSION['tipo'] == "Prestador") {
                if (isset($_SESSION["tipoServico"])) {
                    if($_SESSION['tipoServico'] != "")echo "<h3>". htmlspecialchars($_SESSION["tipoServico"])."</h3>"; 
                    }
                }
            ?>
        </section>
        <hr class="line">
        
        <!--DESCRIÇÃO E NECESSIDADE-->
        <section class="secao">
            <div class="descricao">
                <h1>Descrição</h1>
                <?php 
                    if($_SESSION['descricao'] == null) {
                        if($_SESSION['tipo'] == "Prestador") {
                            echo "<p style='color: red;'>Adicione uma descrição sobre você</p>";
                        } else {
                            echo "<p style='color: red;'>Adicione uma descrição sobre o serviço que você necessita</p>";
                        }
                    } else {
                        echo "<p>" . nl2br(htmlspecialchars($_SESSION['descricao'])) . "</p>";
                    }
                ?>
            </div>
        </section>

        <!--ENDEREÇO-->
        <section class="secao">
            <h1>Endereço</h1>
            <div class="endereco">
                <div class="item"><h3>Bairro</h3>
                    <p><?= htmlspecialchars($_SESSION['bairro']) ?></p>
                </div>
                <div class="item"><h3>Logradouro</h3>
                    <p><?= htmlspecialchars($_SESSION['logradouro']) ?></p>
                </div>
                <div class="item"><h3>Numero</h3>
                    <p><?= htmlspecialchars($_SESSION['numero']) ?></p>
                </div>
                <div class="item"><h3>Complemento</h3>
                    <p>
                        <?php 
                            if($_SESSION['complemento'] != "") {
                                echo htmlspecialchars($_SESSION['complemento']);
                            }
                        ?>
                    </p>
                </div>
            </div>
        </section>

        <!--DADOS DA CONTA-->
        <section class="secao">
            <h1>Dados</h1>
            <div class="dados">
                <div class="item"><h3>Código ID</h3>
                    <p><?= htmlspecialchars($_SESSION['usuario']) ?></p>
                </div>
                <div class="item"><h3>Data de nascimento</h3>
                    <p><?= htmlspecialchars($_SESSION['dataNascimento']) ?></p>
                </div>
                <div class="item"><h3>E-mail</h3>
                    <p><?= htmlspecialchars($_SESSION['email']) ?></p>
                </div>
                <div class="item"><h3>Telefone</h3>
                    <p><?= htmlspecialchars($_SESSION['telefone']) ?></p>
                </div>
            </div>
        </section>
    </section>
    <script src="js/menuLateral.js"></script>
</body>
</html>
