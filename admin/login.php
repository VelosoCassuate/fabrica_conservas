<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Se já estiver logado como admin, redireciona para o dashboard
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$username_preenchido = ''; // Variável para manter o nome de usuário após erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Mantém o nome de usuário preenchido para melhor UX
    $username_preenchido = htmlspecialchars($username); 
    
    if ($auth->authenticateAdmin($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Credenciais inválidas. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

    <style>
        /* Variáveis CSS para fácil mudança de tema */
        :root {
            --primary-color: #2c3e50; /* Azul vibrante */
            --primary-hover: #37526dff;
            --background-color: #f4f7f6; /* Fundo suave */
            --card-background: #ffffff;
            --text-color: #34495e; /* Texto escuro */
            --error-color: #fc9387ff;
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.31);
            --shadow-hover: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .login-container {
            width: 90%;
            max-width: 400px;
            padding: 2.5rem;
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            transition: box-shadow 0.3s ease-in-out;
        }

        .login-container:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .login-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .login-container h2 i {
             margin-right: 10px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .input-icon-wrapper {
            position: relative;
        }
        
        .input-icon-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6; /* Cinza suave */
            pointer-events: none; /* Não interfere no input */
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 12px 12px 40px; /* Mais padding à esquerda para o ícone */
            border: 1px solid #dcdfe6;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box; /* Garante que padding e border não aumentem o tamanho total */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;   
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .message-box {
            padding: 4px;
            border-radius: 8px;
            text-align: center;
        }
        
        .message-error {
            /* background-color: #f8eeecff; */
            color: var(--error-color);
            /* border: 1px solid var(--error-color); */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Administrativo</h2>
        
        
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuário</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" value="<?php echo $username_preenchido; ?>" placeholder="Seu nome de usuário" required autocomplete="username" autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-key"></i>
                    <input type="password" id="password" name="password" placeholder="Sua senha secreta" required autocomplete="current-password">
                    
                </div>
            </div>

            <?php if($erro): ?>
                <div class="message-box message-error"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>