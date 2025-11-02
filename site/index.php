<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Buscar produtos em destaque (os primeiros 3 produtos)
$produtos_destaque = array_slice(getProdutos(), 0, 3);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/site.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .produto-destaque-imagem {
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

        .produto-destaque-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .produto-card:hover .produto-destaque-imagem img {
            transform: scale(1.05);
        }

        .sem-imagem-destaque {
            color: #6c757d;
            font-size: 3rem;
        }

        .produtos-destaque-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
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

        .badge-destaque {
            background: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }

        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-hero {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }

        .btn-hero:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .info-item {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
        }

        .info-item i {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .center {
            text-align: center;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo EMPRESA_NOME; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Início</a></li>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h2>Conservas de Qualidade Superior</h2>
                <p>Há mais de 20 anos produzindo as melhores conservas com ingredientes selecionados</p>
                <a href="produtos.php" class="btn-hero">Conheça Nossos Produtos</a>
            </div>
        </section>

        

        <!-- Produtos em Destaque -->
        <section class="produtos-destaque">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 1rem; color: #2c3e50;">Produtos em Destaque</h2>
                <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                    Conheça alguns dos nossos produtos mais populares
                </p>
                
                <div class="produtos-destaque-grid">
                    <?php foreach($produtos_destaque as $produto): ?>
                        <div class="produto-card">
                            <!-- Imagem do produto -->
                            <div class="produto-destaque-imagem">
                                <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                    <img src="../uploads/<?php echo $produto['imagem']; ?>" 
                                         alt="<?php echo $produto['nome']; ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="sem-imagem-destaque">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Badge de destaque -->
                            <span class="badge-destaque">
                                <i class="fas fa-star"></i> Em Destaque
                            </span>

                            <!-- Badge de exportação -->
                            <?php if($produto['exportacao']): ?>
                                <span class="badge-exportacao" style="background: #3498db; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 0.5rem;">
                                    <i class="fas fa-globe"></i> Exportação
                                </span>
                            <?php endif; ?>

                            <h3><?php echo $produto['nome']; ?></h3>
                            <p class="preco" style="font-weight: bold; color: #e74c3c; font-size: 1.3rem; margin: 0.5rem 0;">
                                <?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT
                            </p>
                            <p style="flex-grow: 1; margin-bottom: 1rem; color: #555;">
                                <?php echo $produto['descricao']; ?>
                            </p>
                            
                            <?php if($produto['exportacao']): ?>
                                <div class="exportacao-info" style="background: #e8f4fd; padding: 1rem; border-radius: 6px; margin-top: 1rem; border-left: 4px solid #3498db;">
                                    <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                                        <strong><i class="fas fa-box"></i> Embalagem Especial</strong>
                                    </p>
                                    <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                                        <strong>Material:</strong> <?php echo $produto['material_embalagem']; ?>
                                    </p>
                                    <?php if($produto['preco_embalagem'] > 0): ?>
                                        <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                                            <strong>Preço da embalagem:</strong> <?php echo number_format($produto['preco_embalagem'], 2, ',', '.'); ?> MT
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(empty($produtos_destaque)): ?>
                    <div class="alert alert-info" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-info-circle"></i> Nenhum produto cadastrado no momento.
                    </div>
                <?php endif; ?>

                <div class="center">
                    <a href="produtos.php" class="btn-hero">
                        <i class="fas fa-arrow-right"></i> Ver Todos os Produtos
                    </a>
                </div>
            </div>
        </section>

        <!-- Informações da Empresa -->
        <section class="info">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #2c3e50;">Nossa Empresa</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <h3>Horário de Atendimento</h3>
                        <p><?php echo EMPRESA_HORARIO; ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Endereço</h3>
                        <p><?php echo EMPRESA_ENDERECO; ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <h3>Telefone</h3>
                        <p><?php echo EMPRESA_TELEFONE; ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <h3>E-mail</h3>
                        <p><?php echo EMPRESA_EMAIL; ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user-tie"></i>
                        <h3>Gerente</h3>
                        <p><?php echo EMPRESA_GERENTE; ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Porque Escolher Nossos Produtos -->
        <section style="background: #f8f9fa; padding: 4rem 0; margin-top: 2rem;">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 3rem; color: #2c3e50;">Porque Escolher Nossos Produtos</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div style="text-align: center;">
                        <div style="background: #3498db; color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3>Qualidade Garantida</h3>
                        <p>Produtos selecionados com os mais altos padrões de qualidade</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="background: #27ae60; color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Ingredientes Naturais</h3>
                        <p>Utilizamos apenas ingredientes frescos e naturais</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="background: #e74c3c; color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Entrega Rápida</h3>
                        <p>Entregamos em todo o país com agilidade e segurança</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="background: #f39c12; color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Suporte 24/7</h3>
                        <p>Atendimento especializado sempre à sua disposição</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #bdc3c7;">
                <i class="fas fa-map-marker-alt"></i> <?php echo EMPRESA_ENDERECO; ?> | 
                <i class="fas fa-phone"></i> <?php echo EMPRESA_TELEFONE; ?> | 
                <i class="fas fa-envelope"></i> <?php echo EMPRESA_EMAIL; ?>
            </p>
        </div>
    </footer>

    <script src="js/site.js"></script>
</body>
</html>