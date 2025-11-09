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
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        /* Variáveis CSS consistentes com o login */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Header */
        header {
            background: var(--card-background);
            box-shadow: var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
        }

        header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 700;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-sm);
            transition: all 0.3s ease;
        }

        nav a:hover,
        nav a.active {
            background: var(--primary-color);
            color: white;
        }

        /* Hero Section - Melhorias Avançadas */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #545b61ff 100%);
            color: white;
            text-align: center;
            padding: 5rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        /* Sistema de Partículas Avançado */
        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Partículas circulares com gradientes */
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float 15s infinite linear;
            background: radial-gradient(circle at 30% 30%, 
                rgba(255, 255, 255, 0.15) 0%, 
                rgba(255, 255, 255, 0.05) 70%);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .particle:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
            animation-duration: 25s;
        }

        .particle:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 70%;
            left: 85%;
            animation-delay: 3s;
            animation-duration: 20s;
        }

        .particle:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 40%;
            left: 80%;
            animation-delay: 6s;
            animation-duration: 30s;
        }

        .particle:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 10%;
            animation-delay: 9s;
            animation-duration: 22s;
        }

        .particle:nth-child(5) {
            width: 60px;
            height: 60px;
            top: 20%;
            left: 75%;
            animation-delay: 12s;
            animation-duration: 18s;
        }

        /* Formas geométricas complexas */
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            animation: float 20s infinite ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .shape:nth-child(6) {
            width: 80px;
            height: 80px;
            top: 15%;
            left: 20%;
            clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
            animation-delay: 2s;
            animation-duration: 28s;
        }

        .shape:nth-child(7) {
            width: 100px;
            height: 100px;
            top: 65%;
            left: 70%;
            clip-path: polygon(20% 0%, 80% 0%, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0% 80%, 0% 20%);
            animation-delay: 5s;
            animation-duration: 24s;
        }

        .shape:nth-child(8) {
            width: 70px;
            height: 70px;
            top: 30%;
            left: 90%;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            animation-delay: 8s;
            animation-duration: 32s;
        }

        .shape:nth-child(9) {
            width: 90px;
            height: 90px;
            top: 75%;
            left: 25%;
            clip-path: circle(50% at 50% 50%);
            animation-delay: 11s;
            animation-duration: 26s;
        }

        .shape:nth-child(10) {
            width: 110px;
            height: 110px;
            top: 10%;
            left: 60%;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: 14s;
            animation-duration: 30s;
        }

        /* Linhas conectivas animadas */
        .connector {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: pulse 8s infinite ease-in-out;
            transform-origin: left center;
        }

        .connector:nth-child(11) {
            width: 200px;
            top: 25%;
            left: 15%;
            animation-delay: 1s;
        }

        .connector:nth-child(12) {
            width: 150px;
            top: 60%;
            left: 40%;
            animation-delay: 4s;
        }

        .connector:nth-child(13) {
            width: 180px;
            top: 45%;
            left: 65%;
            animation-delay: 7s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg) scale(1);
            }
            25% {
                transform: translateY(-25px) rotate(90deg) scale(1.05);
            }
            50% {
                transform: translateY(0) rotate(180deg) scale(1);
            }
            75% {
                transform: translateY(25px) rotate(270deg) scale(0.95);
            }
            100% {
                transform: translateY(0) rotate(360deg) scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.3;
                transform: scaleX(0.8);
            }
            50% {
                opacity: 0.7;
                transform: scaleX(1);
            }
        }

        /* Efeitos de brilho e profundidade */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            z-index: 1;
            animation: glowShift 15s infinite alternate;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.03) 50%, 
                transparent 100%);
            animation: shimmer 12s infinite linear;
            z-index: 1;
        }

        @keyframes glowShift {
            0% {
                opacity: 0.4;
                filter: hue-rotate(0deg);
            }
            50% {
                opacity: 0.7;
                filter: hue-rotate(10deg);
            }
            100% {
                opacity: 0.4;
                filter: hue-rotate(0deg);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 3.2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            animation: fadeInUp 1.2s ease-out 0.2s both;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .hero p {
            font-size: 1.4rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1.2s ease-out 0.4s both;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
            font-weight: 400;
            line-height: 1.7;
        }

        .btn-hero {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: var(--border-radius-sm);
            transition: all 0.4s ease;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 600;
            box-shadow: 
                0 6px 20px rgba(231, 76, 60, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: fadeInUp 1.2s ease-out 0.6s both;
            position: relative;
            overflow: hidden;
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-hero:hover {
            background: var(--secondary-hover);
            transform: translateY(-3px);
            box-shadow: 
                0 10px 30px rgba(231, 76, 60, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .btn-hero:hover::before {
            left: 100%;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efeito de partículas interativas */
        .hero-interaction {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            pointer-events: none;
        }

        .interactive-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            opacity: 0;
            animation: sparkle 2s ease-out forwards;
        }

        @keyframes sparkle {
            0% {
                opacity: 0;
                transform: scale(0);
            }
            50% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(0) translateY(-20px);
            }
        }

        /* Restante do CSS permanece igual */
        /* Produtos em Destaque */
        .produtos-destaque {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .produtos-destaque-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            margin: 3rem 0;
        }

        .produto-card {
            background: var(--card-background);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .produto-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .produto-destaque-imagem {
            width: 100%;
            height: 250px;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            background: var(--background-color);
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
            transform: scale(1.08);
        }

        .sem-imagem-destaque {
            color: #bdc3c7;
            font-size: 4rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            margin-right: 0.5rem;
        }

        .badge-destaque {
            background: var(--secondary-color);
            color: white;
        }

        .badge-exportacao {
            background: var(--info-color);
            color: white;
        }

        .produto-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .preco {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.5rem;
            margin: 1rem 0;
        }

        .produto-card p {
            flex-grow: 1;
            margin-bottom: 1.5rem;
            color: var(--text-light);
            line-height: 1.6;
        }

        .exportacao-info {
            background: #e8f4fd;
            padding: 1.2rem;
            border-radius: var(--border-radius-sm);
            margin-top: 1rem;
            border-left: 4px solid var(--info-color);
        }

        .exportacao-info p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: var(--text-color);
        }

        /* Informações da Empresa */
        .info {
            padding: 4rem 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .info-item {
            background: var(--card-background);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            text-align: center;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .info-item i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .info-item h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .info-item p {
            color: var(--text-light);
            line-height: 1;
        }

        /* Porque Escolher */
        .porque-escolher {
            background: var(--card-background);
            padding: 5rem 0;
            margin-top: 2rem;
        }

        .beneficios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .beneficio-item {
            text-align: center;
            padding: 0 1rem;
        }

        .beneficio-icon {
            background: var(--primary-color);
            color: white;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            transition: all 0.3s ease;
        }

        .beneficio-item:hover .beneficio-icon {
            transform: scale(1.1);
            background: var(--primary-hover);
        }

        .beneficio-item h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .beneficio-item p {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        footer .container {
            text-align: center;
        }

        footer p {
            margin-bottom: 0.5rem;
        }

        .footer-info {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .footer-info i {
            margin-right: 0.5rem;
        }

        /* Utilitários */
        .center {
            text-align: center;
            margin: 3rem 0;
        }

        .alert {
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            text-align: center;
            margin: 2rem 0;
        }

        .alert-info {
            background: #e8f4fd;
            color: var(--info-color);
            border-left: 4px solid var(--info-color);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                gap: 1rem;
            }

            nav ul {
                gap: 1rem;
            }

            .hero h2 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .produtos-destaque-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .particle, .shape {
                transform: scale(0.7);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            nav ul {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .hero {
                padding: 3rem 0;
            }

            .hero h2 {
                font-size: 1.8rem;
            }

            .particle, .shape {
                transform: scale(0.5);
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
                    <li><a href="index.php" class="active">Início</a></li>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section Melhorada -->
        <section id="hero" class="hero">
            <!-- Sistema de partículas avançado -->
            <div class="hero-particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="connector"></div>
                <div class="connector"></div>
                <div class="connector"></div>
            </div>
            
            <!-- Camada de interação -->
            <div class="hero-interaction" id="heroInteraction"></div>
            
            <div class="container">
                <h2>Conservas de Qualidade Superior</h2>
                <p>Há mais de 20 anos produzindo as melhores conservas com ingredientes selecionados e processos rigorosos de qualidade</p>
                <a href="produtos.php" class="btn-hero">Conheça Nossos Produtos</a>
            </div>
        </section>

        <!-- Restante do conteúdo permanece igual -->
        <!-- Produtos em Destaque -->
        <section class="produtos-destaque">
            <div class="container">
                <h2 class="section-title">Produtos em Destaque</h2>
                <p class="section-subtitle">
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
                            <span class="badge badge-destaque">
                                <i class="fas fa-star"></i> Em Destaque
                            </span>

                            <!-- Badge de exportação -->
                            <?php if($produto['exportacao']): ?>
                                <span class="badge badge-exportacao">
                                    <i class="fas fa-globe"></i> Exportação
                                </span>
                            <?php endif; ?>

                            <h3><?php echo $produto['nome']; ?></h3>
                            <p class="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT</p>
                            <p><?php echo $produto['descricao']; ?></p>
                            
                            <?php if($produto['exportacao']): ?>
                                <div class="exportacao-info">
                                    <p><strong><i class="fas fa-box"></i> Embalagem Especial</strong></p>
                                    <p><strong>Material:</strong> <?php echo $produto['material_embalagem']; ?></p>
                                    <?php if($produto['preco_embalagem'] > 0): ?>
                                        <p><strong>Preço da embalagem:</strong> <?php echo number_format($produto['preco_embalagem'], 2, ',', '.'); ?> MT</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(empty($produtos_destaque)): ?>
                    <div class="alert alert-info">
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
                <h2 class="section-title">Nossa Empresa</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <i style="color: #3498db;" class="fas fa-clock"></i>
                        <h3>Horário de Atendimento</h3>
                        <p><?php echo EMPRESA_HORARIO; ?></p>
                    </div>
                    <div class="info-item">
                        <i style="color: #3498db;" class="fas fa-map-marker-alt"></i>
                        <h3>Endereço</h3>
                        <p><?php echo EMPRESA_ENDERECO; ?></p>
                    </div>
                    <div class="info-item">
                        <i style="color: #3498db;" class="fas fa-phone"></i>
                        <h3>Telefone</h3>
                        <p><?php echo EMPRESA_TELEFONE; ?></p>
                    </div>
                    <div class="info-item">
                        <i style="color: #3498db;" class="fas fa-envelope"></i>
                        <h3>E-mail</h3>
                        <p><?php echo EMPRESA_EMAIL; ?></p>
                    </div>
                    <div class="info-item">
                        <i style="color: #3498db;" class="fas fa-user-tie"></i>
                        <h3>Gerente</h3>
                        <p><?php echo EMPRESA_GERENTE; ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Porque Escolher Nossos Produtos -->
        <section class="porque-escolher">
            <div class="container">
                <h2 class="section-title">Porque Escolher Nossos Produtos</h2>
                <div class="beneficios-grid">
                    <div class="beneficio-item">
                        <div class="beneficio-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3>Qualidade Garantida</h3>
                        <p>Produtos selecionados com os mais altos padrões de qualidade</p>
                    </div>
                    <div class="beneficio-item">
                        <div class="beneficio-icon" style="background: var(--success-color);">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Ingredientes Naturais</h3>
                        <p>Utilizamos apenas ingredientes frescos e naturais</p>
                    </div>
                    <div class="beneficio-item">
                        <div class="beneficio-icon" style="background: var(--secondary-color);">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Entrega Rápida</h3>
                        <p>Entregamos em todo o país com agilidade e segurança</p>
                    </div>
                    <div class="beneficio-item">
                        <div class="beneficio-icon" style="background: var(--warning-color);">
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
            <div class="footer-info">
                <i class="fas fa-map-marker-alt"></i> <?php echo EMPRESA_ENDERECO; ?> | 
                <i class="fas fa-phone"></i> <?php echo EMPRESA_TELEFONE; ?> | 
                <i class="fas fa-envelope"></i> <?php echo EMPRESA_EMAIL; ?>
            </div>
        </div>
    </footer>

    <script>
        // Interatividade para o hero section
        document.addEventListener('DOMContentLoaded', function() {
            const heroInteraction = document.getElementById('heroInteraction');
            let mouseX = 0;
            let mouseY = 0;
            
            // Rastrear movimento do mouse
            document.getElementById('hero').addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY - 50;
                
                // Criar partículas interativas ocasionalmente
                if (Math.random() > 0.2) {
                    createInteractiveParticle(mouseX, mouseY);
                }
            });
            
            // Criar partículas interativas no clique
            document.addEventListener('click', function(e) {
                createInteractiveParticle(e.clientX, e.clientY);
            });
            
            function createInteractiveParticle(x, y) {
                const particle = document.createElement('div');
                particle.className = 'interactive-particle';
                particle.style.left = x + 'px';
                particle.style.top = (y - 20) + 'px';
                
                heroInteraction.appendChild(particle);
                
                // Remover após a animação
                setTimeout(() => {
                    particle.remove();
                }, 2000);
            }
            
            // Efeito parallax sutil nas partículas
            document.addEventListener('mousemove', function(e) {
                const particles = document.querySelectorAll('.particle, .shape');
                const moveX = (e.clientX - window.innerWidth / 2) / 50;
                const moveY = (e.clientY - window.innerHeight / 2) / 50;
                
                particles.forEach(particle => {
                    const speed = parseFloat(particle.style.width) / 20;
                    particle.style.transform = `translate(${moveX * speed}px, ${moveY * speed}px)`;
                });
            });
        });
    </script>
</body>
</html>