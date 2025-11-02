<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$produtos = getProdutos();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/site.css">
    <style>
        .produto-imagem {
            width: 100%;
            height: 200px;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
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
            color: #6c757d;
            font-size: 3rem;
        }

        .produto-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .preco {
            font-weight: bold;
            color: #e74c3c;
            font-size: 1.3rem;
            margin: 0.5rem 0;
        }

        .exportacao-info {
            background: #e8f4fd;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            border-left: 4px solid #3498db;
        }

        .exportacao-info p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
        }

        .badge-exportacao {
            background: #3498db;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
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
                <h2>Nossos Produtos</h2>
                <p class="text-center" style="margin-bottom: 2rem; color: #666;">
                    Conheça nossa linha completa de conservas de alta qualidade
                </p>
                
                <div class="produtos-grid">
                    <?php foreach($produtos as $produto): ?>
                        <div class="produto-card">
                            <!-- Imagem do produto -->
                            <div class="produto-imagem">
                                <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                    <img src="../uploads/<?php echo $produto['imagem']; ?>" 
                                         alt="<?php echo $produto['nome']; ?>">
                                <?php else: ?>
                                    <div class="sem-imagem">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Badge de exportação -->
                            <?php if($produto['exportacao']): ?>
                                <span class="badge-exportacao">
                                    <i class="fas fa-globe"></i> Produto de Exportação
                                </span>
                            <?php endif; ?>

                            <h3><?php echo $produto['nome']; ?></h3>
                            <p class="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT</p>
                            <p style="flex-grow: 1; margin-bottom: 1rem;"><?php echo $produto['descricao']; ?></p>
                            
                            <?php if($produto['exportacao']): ?>
                                <div class="exportacao-info">
                                    <p><strong><i class="fas fa-box"></i> Embalagem Especial</strong></p>
                                    <p><strong>Material:</strong> <?php echo $produto['material_embalagem']; ?></p>
                                    <p><strong>Preço da embalagem:</strong> <?php echo number_format($produto['preco_embalagem'], 2, ',', '.'); ?> MT</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(empty($produtos)): ?>
                    <div class="alert alert-info" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-info-circle"></i> Nenhum produto cadastrado no momento.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Adicionar Font Awesome para os ícones -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="js/site.js"></script>
</body>
</html>