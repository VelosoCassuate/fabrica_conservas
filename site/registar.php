<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();

if(isset($_SESSION['cliente_id'])) {
    header('Location: cliente_area.php');
    exit;
}

$erro = '';
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validações básicas
    if(empty($nome) || empty($email)) {
        $erro = 'Por favor, preencha todos os campos!';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, insira um email válido!';
    } else {
      registarCliente($nome, $email);
      
      $sucesso = 'Registro recebido com sucesso! Entraremos em contato em breve.';
      
      $nome = '';
      $email = '';

    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/site.css">
    <style>
        .registro-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .erro {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .sucesso {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo EMPRESA_NOME; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="registro">
          <h2 style="padding: 10px 26px;">Registro de Novo Cliente</h2>
                    
            <div class="container">
                <div class="registro-container">
                    
                    <?php if($erro): ?>
                        <div class="erro"><?php echo $erro; ?></div>
                    <?php endif; ?>
                    
                    <?php if($sucesso): ?>
                        <div class="sucesso"><?php echo $sucesso; ?></div>
                    <?php endif; ?>
                    
                    <form style="background-color: #fff; border-radius: 12px; padding: 24px; box-shadow: 0px 5px 10px #adadadff;margin-bottom: 12px;" method="POST" class="registro-form">
                        <div class="form-group">
                            <label for="nome">Nome Completo:</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Registrar</button>
                            <a style="padding: 8px; background-color: #2c3e50;" href="cliente_login.php" class="btn btn-secondary">Voltar ao Login</a>
                        </div>
                    </form>
                    
                    <p class="info-text">
                        Ao se registrar, você receberá uma chave de acesso por email para acessar a área do cliente.
                    </p>
                </div>
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