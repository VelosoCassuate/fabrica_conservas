<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Se já estiver logado como admin, redireciona para o dashboard
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->authenticateAdmin($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Credenciais inválidas!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .login-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        
        .btn-login:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Administrativo</h2>
        
        <?php if($erro): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div style="margin-top: 2rem; text-align: center;">
            <p><strong>Credenciais de Teste:</strong></p>
            <p>Usuário: admin | Senha: admin123</p>
            <p>Usuário: gerente | Senha: gerente123</p>
        </div>
    </div>
</body>
</html>