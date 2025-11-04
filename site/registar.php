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
$sucesso = '';
// Inicializa as variáveis para manter os dados no formulário em caso de erro
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validações básicas
    if(empty($nome) || empty($email)) {
        $erro = 'Por favor, preencha todos os campos!';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, insira um email válido!';
    } else {
      // Supondo que registarCliente() faz a inserção e pode gerar a chave de acesso.
      // Neste cenário, a chave é enviada offline/por e-mail posteriormente.
      registarCliente($nome, $email); 
      
      $sucesso = 'Registro recebido com sucesso! Entraremos em contato em breve para fornecer a sua chave de acesso.';
      
      // Limpa as variáveis do formulário após o sucesso
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
            color: white;
            white-space: nowrap; /* Impede que o texto quebre em botões curtos */
        }
        .btn-primary { /* Botão principal de registro */
            background: var(--success-color);
        }
        .btn-primary:hover {
            background: #229954;
            transform: translateY(-2px);
        }
        
        .btn-secondary { /* Botão Voltar ao Login */
            background: var(--primary-color) !important; /* Sobrescreve o estilo original */
        }
        .btn-secondary:hover {
            background: var(--primary-hover) !important;
            transform: translateY(-2px);
        }

        /* Layout da Seção de Registro */
        .registro {
            padding: 4rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 60vh;
        }

        .registro h2 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 0.5rem;
        }
        
        .registro-container {
            max-width: 500px;
            padding: 0; /* Remove padding desnecessário aqui */
            margin: 0 auto;
        }

        /* Estilo do Formulário */
        .registro-form {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 2rem; /* Mais padding interno */
            box-shadow: var(--shadow-light);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.2);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .form-actions .btn {
            flex: 1;
        }

        /* Estilos de Alerta */
        .erro {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-sm);
            border: 1px solid #f5c6cb;
            font-weight: 500;
        }
        .sucesso {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-sm);
            border: 1px solid #c3e6cb;
            font-weight: 500;
        }
        
        .info-text {
            margin-top: 1rem;
            font-size: 0.95rem;
            color: var(--text-light);
            text-align: center;
            border-top: 1px dashed #ddd;
            padding-top: 1rem;
        }
        
        @media (max-width: 450px) {
             .form-actions {
                flex-direction: column;
            }
             .form-actions .btn {
                width: 100%;
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
        <section class="registro">
            <div class="container">
                <div class="registro-container">
                    
                    <h2><i class="fas fa-user-plus"></i> Registro de Novo Cliente</h2>
                    
                    <?php if($erro): ?>
                        <div class="erro"><i class="fas fa-exclamation-triangle"></i> **<?php echo $erro; ?>**</div>
                    <?php endif; ?>
                    
                    <?php if($sucesso): ?>
                        <div class="sucesso"><i class="fas fa-check-circle"></i> **<?php echo $sucesso; ?>**</div>
                    <?php endif; ?>
                    
                    <form method="POST" class="registro-form">
                        <div class="form-group">
                            <label for="nome">Nome Completo:</label>
                            <input type="text" 
                                   id="nome" 
                                   name="nome" 
                                   value="<?php echo htmlspecialchars($nome); ?>" 
                                   required 
                                   placeholder="Nome da Empresa ou Pessoa">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   required 
                                   placeholder="seu.email@exemplo.com">
                        </div>
                        
                        <p style="font-size: 0.9rem; color: var(--text-light); margin-top: -0.5rem; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> Os dados serão submetidos para aprovação.
                        </p>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar Registro
                            </button>
                            <a href="cliente_login.php" class="btn btn-secondary">
                                <i class="fas fa-sign-in-alt"></i> Voltar ao Login
                            </a>
                        </div>
                    </form>
                    
                    <p class="info-text">
                        A **chave de acesso** necessária para o login será enviada por e-mail após a confirmação e aprovação do seu registro pela nossa equipa.
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

    </body>
</html>