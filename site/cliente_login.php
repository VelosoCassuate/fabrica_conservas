<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();

// Se o cliente já estiver logado, redireciona
if(isset($_SESSION['cliente_id'])) {
    header('Location: cliente_area.php');
    exit;
}

$erro = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chave_acesso = $_POST['chave_acesso'] ?? '';
    
    // Supondo que autenticarCliente() esteja definido em includes/functions.php
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
    <title>Login Cliente - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    
    <style>
        /* Variáveis CSS globais para consistência */
        :root {
            --primary-color: #2c3e50;
            --primary-hover: #37526dff;
            --secondary-color: #e74c3c;
            --secondary-hover: #c0392b;
            --background-color: #f4f7f6;
            --card-background: #ffffff;
            --text-color: #34495e;
            --text-light: #666;
            --error-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --border-radius-sm: 8px;
        }

        /* Estilos base (body, container) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--background-color); color: var(--text-color); line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

        /* Header (Consistente com outras páginas) */
        header { background: var(--card-background); box-shadow: var(--shadow-light); position: sticky; top: 0; z-index: 1000; }
        header .container { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; }
        header h1 { color: var(--primary-color); font-size: 1.8rem; font-weight: 700; }
        nav ul { display: flex; list-style: none; gap: 2rem; }
        nav a { text-decoration: none; color: var(--text-color); font-weight: 600; padding: 0.5rem 1rem; border-radius: var(--border-radius-sm); transition: all 0.3s ease; }
        nav a:hover, nav a.active { background: var(--primary-color); color: white; }
        
        /* Footer (Consistente com outras páginas) */
        footer { background: var(--primary-color); color: white; padding: 1.5rem 0; margin-top: 4rem; text-align: center; }

        /* Estilos de Botão */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: 600;
            text-align: center;
            color: white; /* Cor do texto padrão para botões */
        }
        .btn-primary-bg { /* Estilo para o botão Entrar (Cor primária) */
            background: var(--primary-color);
        }
        .btn-primary-bg:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-secondary-bg { /* Estilo para o botão Registrar (Cor secundária/contraste) */
            background: var(--info-color);
        }
        .btn-secondary-bg:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Layout da Seção de Login */
        .login {
            padding: 4rem 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh; /* Altura mínima para centralizar */
        }

        .login .container {
            max-width: 450px; /* Largura máxima para o formulário */
            padding: 2rem;
            background: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            text-align: center;
        }

        .login h2 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        /* Estilo do Formulário */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .login-form .btn {
            width: 100%; /* Botões de largura total */
            margin-top: 0.5rem;
        }
        
        .login-form .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-top: 1rem;
        }
        
        .login-form .action-buttons .btn {
             flex: 1;
        }

        /* Estilo para Mensagem de Erro */
        .erro {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-sm);
            border: 1px solid #f5c6cb;
            font-weight: 500;
        }
        
        .info-text {
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-light);
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        
        /* Responsividade para botões em telas muito pequenas */
        @media (max-width: 400px) {
             .login-form .action-buttons {
                flex-direction: column;
            }
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
                    <li><a href="cliente_login.php" class="active">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="login">
            <div class="container">
                <h2><i class="fas fa-user-lock"></i> Acesso à Área do Cliente</h2>
                
                <?php if($erro): ?>
                    <div class="erro"><i class="fas fa-exclamation-triangle"></i> **<?php echo $erro; ?>**</div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="chave_acesso">Chave de Acesso:</label>
                        <input type="password" 
                               id="chave_acesso" 
                               name="chave_acesso" 
                               placeholder="Digite sua chave fornecida" 
                               required>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary-bg">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </button>
                        <a href="registar.php" class="btn btn-secondary-bg">
                            <i class="fas fa-user-plus"></i> Registar
                        </a>
                    </div>
                </form>
                
                <p class="info-text">
                    <i class="fas fa-shield-alt"></i> Apenas clientes autorizados e aprovados têm acesso a esta área. Entre em contato conosco para obter ou ativar sua chave de acesso.
                </p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    </body>
</html>