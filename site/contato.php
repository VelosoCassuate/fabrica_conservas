<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$mensagem = '';
$tipo_mensagem = '';
// Inicializa variáveis do formulário para persistir dados em caso de erro
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$mensagem_texto = $_POST['mensagem'] ?? '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $mensagem_texto = trim($_POST['mensagem'] ?? '');
    
    // Validação de e-mail adicional
    if(empty($nome) || empty($email) || empty($tipo) || empty($mensagem_texto)) {
        $mensagem = 'Por favor, preencha todos os campos.';
        $tipo_mensagem = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Por favor, insira um email válido.';
        $tipo_mensagem = 'erro';
    } else {
        // Supondo que salvarReclamacao() está definido em includes/functions.php
        if(salvarReclamacao($nome, $email, $tipo, $mensagem_texto)) {
            $mensagem = 'Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.';
            $tipo_mensagem = 'sucesso';
            
            // Limpa o formulário após o sucesso
            $nome = '';
            $email = '';
            $tipo = '';
            $mensagem_texto = '';
        } else {
            $mensagem = 'Erro ao enviar mensagem. Tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - <?php echo SITE_NAME; ?></title>
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
            background: var(--secondary-color); /* Botão de Ação Principal (Contato) */
        }
        .btn:hover {
            background: var(--secondary-hover);
            transform: translateY(-2px);
        }

        /* Layout da Seção de Contato */
        .contato {
            padding: 2rem 0;
        }

        .contato h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1rem;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        /* Grid de Informações e Formulário */
        .contato-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start; /* Alinha o topo dos elementos */
        }
        
        .contato-info, .contato-form {
            background: var(--card-background);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            flex: 1;
        }
        
        /* Estilos de Informações de Contato */
        .contato-info {
            flex: 1; /* Ocupa 1/3 */
            order: 1; /* Define a ordem para telas maiores */
        }
        
        .contato-info h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 0.5rem;
        }
        
        .contato-info p {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-color);
        }
        .contato-info strong {
             font-weight: 600;
        }
        
        /* Estilos do Formulário de Contato */
        .contato-form {
            flex: 2; /* Ocupa 2/3 */
            order: 2;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        select {
            appearance: none; /* Remove o estilo padrão do select */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232c3e50%22%20d%3D%22M287%20177.3c-3.6-3.6-7.8-5.3-12.7-5.3H18.1c-4.9%200-9.1%201.7-12.7%205.3-3.6%203.6-5.3%207.8-5.3%2012.7s1.7%209.1%205.3%2012.7L146.2%20292c3.6%203.6%207.8%205.3%2012.7%205.3s9.1-1.7%2012.7-5.3L287%20202.7c3.6-3.6%205.3-7.8%205.3-12.7s-1.7-9.1-5.3-12.7z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 0.8rem auto;
            padding-right: 2.5rem; /* Espaço para a seta */
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.2);
        }
        
        textarea {
            resize: vertical;
        }

        .contato-form .btn {
            width: 100%;
            margin-top: 1rem;
        }

        /* Estilos de Alerta */
        .erro, .sucesso {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
        }
        .erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .contato-content {
                flex-direction: column;
            }
            .contato-info, .contato-form {
                flex: none; /* Desativa o flex-grow */
                width: 100%;
                order: initial;
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
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php" class="active">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="contato">
            <div class="container">
                <h2><i class="fas fa-headset"></i> Entre em Contato Conosco</h2>
                
                <?php if($mensagem): ?>
                    <div class="<?php echo $tipo_mensagem; ?>">
                        <?php 
                            $icon = ($tipo_mensagem == 'sucesso') ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
                            echo "$icon $mensagem"; 
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="contato-content">
                    <div class="contato-info">
                        <h3><i class="fas fa-map-marker-alt"></i> Informações de Contato</h3>
                        <p><strong><i class="fas fa-location-arrow"></i> Endereço:</strong> <?php echo EMPRESA_ENDERECO ?? 'Não disponível'; ?></p>
                        <p><strong><i class="fas fa-phone"></i> Telefone:</strong> <?php echo EMPRESA_TELEFONE ?? 'Não disponível'; ?></p>
                        <p><strong><i class="fas fa-envelope"></i> E-mail:</strong> <?php echo EMPRESA_EMAIL ?? 'Não disponível'; ?></p>
                        <p><strong><i class="fas fa-clock"></i> Horário:</strong> <?php echo EMPRESA_HORARIO ?? 'Não disponível'; ?></p>
                        <p><strong><i class="fas fa-user-tie"></i> Gerente:</strong> <?php echo EMPRESA_GERENTE ?? 'Não disponível'; ?></p>
                    </div>
                    
                    <form method="POST" class="contato-form">
                        <h3><i class="fas fa-comment-dots"></i> Envie uma Mensagem</h3>
                        
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required placeholder="Seu nome completo">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="seu.email@exemplo.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo">Tipo de Mensagem:</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="reclamacao" <?php echo ($tipo == 'reclamacao' ? 'selected' : ''); ?>>Reclamação</option>
                                <option value="sugestao" <?php echo ($tipo == 'sugestao' ? 'selected' : ''); ?>>Sugestão</option>
                                <option value="outros" <?php echo ($tipo == 'outros' ? 'selected' : ''); ?>>Outros Assuntos</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensagem">Mensagem:</label>
                            <textarea id="mensagem" name="mensagem" rows="5" required placeholder="Escreva aqui a sua mensagem..."><?php echo htmlspecialchars($mensagem_texto); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane"></i> Enviar Mensagem
                        </button>
                    </form>
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