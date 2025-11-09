<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Verificar se está logado como administrador
requireAdminAuth();

$page_title = $page_title ?? 'Administração';
$current_page = $current_page ?? 'dashboard';

// Inicializar variáveis de mensagem
$mensagem_sucesso = $mensagem_sucesso ?? '';
$mensagem_erro = $mensagem_erro ?? '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <!-- Font Awesome Offline -->
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .alert {
            padding: 12px 16px;
            margin: 15px 0;
            border-radius: 6px;
            border-left: 4px solid;
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-auto-hide {
            animation: slideIn 0.3s ease-out, fadeOut 5s 2s ease-in forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                height: 0;
                padding: 0;
                margin: 0;
                overflow: hidden;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1><?php echo EMPRESA_NOME; ?></h1>
                <div class="subtitle">Painel Administrativo</div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a></li>
                    <li><a href="analise_producao.php" class="<?php echo $current_page == 'analise' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Análise de Produção
                        </a></li>
                    <li><a href="consulta_produtos.php" class="<?php echo $current_page == 'produtos' ? 'active' : ''; ?>">
                            <i class="fas fa-boxes"></i> Consulta de Produtos
                        </a></li>
                    <li><a href="reclamacoes.php" class="<?php echo $current_page == 'reclamacoes' ? 'active' : ''; ?>">
                            <i class="fas fa-comments"></i> Reclamações
                        </a></li>
                    <li><a href="gestao_arquivos.php" class="<?php echo $current_page == 'arquivos' ? 'active' : ''; ?>">
                            <i class="fas fa-file-upload"></i> Gestão de Arquivos
                        </a></li>
                    <li><a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a></li>
                </ul>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="content-header">
                <h2><?php echo $page_title; ?></h2>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo getUserInfo()['username']; ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                </div>
            </div>

            <!-- Notificações -->
            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert alert-success alert-auto-hide" id="alert-sucesso">
                    <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alert alert-error" id="alert-erro">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $mensagem_erro; ?>
                </div>
            <?php endif; ?>

            <!-- CONTEÚDO ESPECÍFICO DA PÁGINA SERÁ INCLUÍDO AQUI -->
            <?php echo $content; ?>

        </main>
    </div>

    <!-- Chart.js Offline -->
    <script src="../assets/chartjs/chart.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // Auto-esconder notificações de sucesso após 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('alert-sucesso');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 5000);
            }

            // Fechar notificações manualmente
            document.addEventListener('click', function(e) {
                if (e.target.closest('.alert')) {
                    e.target.closest('.alert').style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>