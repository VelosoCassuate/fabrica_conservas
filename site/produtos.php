<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Busca todos os produtos para exibir na página
$produtos = getProdutos();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    
    <style>
        /* Variáveis CSS consistentes com o index e contato */
        :root {
            --primary-color: #2c3e50;
            --primary-hover: #37526dff;
            --secondary-color: #e74c3c;
            --secondary-hover: #c0392b;
            --background-color: #f4f7f6;
            --card-background: #ffffff;
            --text-color: #34495e;
            --text-light: #666;
            --error-color: #fc9387ff;
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

        /* Header (Copied from index.php) */
        header { background: var(--card-background); box-shadow: var(--shadow-light); position: sticky; top: 0; z-index: 1000; }
        header .container { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; }
        header h1 { color: var(--primary-color); font-size: 1.8rem; font-weight: 700; }
        nav ul { display: flex; list-style: none; gap: 2rem; }
        nav a { text-decoration: none; color: var(--text-color); font-weight: 600; padding: 0.5rem 1rem; border-radius: var(--border-radius-sm); transition: all 0.3s ease; }
        nav a:hover, nav a.active { background: var(--primary-color); color: white; }

        /* Estilos da Seção de Produtos */
        .produtos {
            padding: 2rem 0;
        }
        
        /* Títulos e Subtítulos */
        .produtos h2 {
            /* text-align: center; */
            /* margin-bottom: 1rem; */
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
        }
        .produtos .text-center {
            text-align: center;
            color: var(--text-light);
        }
        
        /* Grid de Produtos */
        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        /* Card do Produto - Adaptado para consistência */
        .produto-card {
            background: var(--card-background); /* Usando variável */
            padding: 1.5rem;
            border-radius: var(--border-radius); /* Usando variável */
            box-shadow: var(--shadow-light); /* Usando variável */
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover); /* Usando variável */
        }
        
        /* Imagem */
        .produto-imagem {
            width: 100%;
            height: 200px;
            margin-bottom: 1rem;
            border-radius: var(--border-radius-sm); /* Usando variável */
            overflow: hidden;
            background: var(--background-color); /* Usando variável */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .produto-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .produto-card:hover .produto-imagem img {
            transform: scale(1.05);
        }

        .sem-imagem {
            color: #bdc3c7; /* Cor mais suave para ícones placeholders */
            font-size: 3rem;
        }

        /* Título do Produto */
        .produto-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.4rem;
            font-weight: 700;
        }

        /* Preço - Usando secondary-color para destaque */
        .preco {
            font-weight: 700; /* Alterado de bold para 700 */
            color: var(--secondary-color); /* Usando variável */
            font-size: 1.5rem; /* Aumentado um pouco para destaque */
            margin: 0.5rem 0 1rem;
        }
        
        /* Descrição */
        .produto-card p {
            flex-grow: 1; 
            margin-bottom: 1rem;
            color: var(--text-light); /* Usando variável */
        }
        
        /* Badge de Exportação */
        .badge-exportacao {
            background: var(--info-color); /* Usando variável */
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        /* Info de Exportação */
        .exportacao-info {
            background: #e8f4fd;
            padding: 1rem;
            border-radius: var(--border-radius-sm); /* Usando variável */
            margin-top: 1rem;
            border-left: 4px solid var(--info-color); /* Usando variável */
        }

        .exportacao-info p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
            color: var(--text-color);
        }
        
        /* Alerta de Vazio */
        .alert {
             padding: 1.5rem;
             border-radius: var(--border-radius-sm);
             text-align: center;
             margin: 2rem auto;
             background: #e8f4fd;
             color: var(--info-color);
             border-left: 4px solid var(--info-color);
             max-width: 600px;
        }

        /* Footer (Copiado do index.php) */
        footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        footer .container {
            text-align: center;
        }
        
        /* Responsividade (Copiado do index.php) */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                gap: 1rem;
            }

            nav ul {
                gap: 1rem;
            }

            .produtos h2 {
                font-size: 2rem;
            }
            
            .produtos-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
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
                    <li><a href="produtos.php" class="active">Produtos</a></li>
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="produtos">
            <div class="container">
                <h2 style="text-align: left;">Nossos Produtos</h2>
                <p style="margin-bottom: 1rem; color: var(--text-light);">
                    Conheça nossa linha completa de conservas de alta qualidade
                </p>
                
                <div class="produtos-grid">
                    <?php if (!empty($produtos)): ?>
                        <?php foreach($produtos as $produto): ?>
                            <div class="produto-card">
                                <div class="produto-imagem">
                                    <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                        <img src="../uploads/<?php echo $produto['imagem']; ?>" 
                                            alt="<?php echo $produto['nome']; ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="sem-imagem">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if(isset($produto['exportacao']) && $produto['exportacao']): ?>
                                    <span class="badge-exportacao">
                                        <i class="fas fa-globe"></i> Produto de Exportação
                                    </span>
                                <?php endif; ?>

                                <h3><?php echo $produto['nome']; ?></h3>
                                <p class="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT</p>
                                <p style="flex-grow: 1; margin-bottom: 1rem;"><?php echo $produto['descricao']; ?></p>
                                
                                <?php if(isset($produto['exportacao']) && $produto['exportacao']): ?>
                                    <div class="exportacao-info">
                                        <p><strong><i class="fas fa-box"></i> Embalagem Especial</strong></p>
                                        <p><strong>Material:</strong> <?php echo $produto['material_embalagem'] ?? 'N/A'; ?></p>
                                        <?php 
                                            $preco_embalagem = $produto['preco_embalagem'] ?? 0;
                                            if ($preco_embalagem > 0): 
                                        ?>
                                            <p><strong>Preço da embalagem:</strong> <?php echo number_format($preco_embalagem, 2, ',', '.'); ?> MT</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum produto cadastrado no momento.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
             <div class="footer-info">
                <i class="fas fa-map-marker-alt"></i> <?php echo EMPRESA_ENDERECO ?? 'Endereço da Empresa'; ?> | 
                <i class="fas fa-phone"></i> <?php echo EMPRESA_TELEFONE ?? 'Telefone da Empresa'; ?> | 
                <i class="fas fa-envelope"></i> <?php echo EMPRESA_EMAIL ?? 'Email da Empresa'; ?>
            </div>
        </div>
    </footer>

    <script src="js/site.js"></script>
</body>
</html>