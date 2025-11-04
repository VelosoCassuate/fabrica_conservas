<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();

if(isset($_SESSION['cliente_id'])) {
    header('Location: cliente_area.php');
    exit;
}

$erro = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chave_acesso = $_POST['chave_acesso'] ?? '';
    
    $cliente = autenticarCliente($chave_acesso);
    if($cliente) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];
        header('Location: cliente_area.php');
        exit;
    } else {
        $erro = 'Chave de acesso inválida!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/site.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo EMPRESA_NOME; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="cliente_login.php" class="active">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="login">
            <div class="container">
                <h2>Área do Cliente</h2>
                <?php if($erro): ?>
                    <div class="erro"><?php echo $erro; ?></div>
                <?php endif; ?>
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="chave_acesso">Chave de Acesso:</label>
                        <input type="password" id="chave_acesso" name="chave_acesso">
                    </div>
                    <button type="submit" class="btn">Entrar</button>
                    <a style="padding: 8px; background-color: #2c3e50;" class="btn" href="registar.php">Registar</a>
                </form>
                <p class="info-text">Apenas clientes autorizados têm acesso a esta área. Entre em contato conosco para obter sua chave de acesso.</p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/site.js"></script>
</body>
</html>