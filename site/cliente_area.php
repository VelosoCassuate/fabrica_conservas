<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Redireciona se o cliente não estiver logado
if(!isset($_SESSION['cliente_id'])) {
    header('Location: cliente_login.php');
    exit;
}

$produtos = getProdutos();
$mensagem = '';

// Buscar proformas do cliente (EXCLUINDO CANCELADAS)
$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, 
                 COUNT(pi.id) as total_itens, 
                 COALESCE(SUM(pi.quantidade * prod.preco), 0) as valor_total
          FROM proformas p
          LEFT JOIN proforma_itens pi ON p.id = pi.proforma_id
          LEFT JOIN produtos prod ON pi.produto_id = prod.id
          WHERE p.cliente_id = ? 
          AND p.status IN ('pendente', 'confirmada')
          GROUP BY p.id
          ORDER BY 
            CASE p.status 
                WHEN 'pendente' THEN 1
                WHEN 'confirmada' THEN 2
            END,
            p.data_criacao DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(1, $_SESSION['cliente_id']);
$stmt->execute();
$proformas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['criar_proforma'])) {
    $itens = [];
    foreach($_POST['quantidade'] as $produto_id => $quantidade) {
        // Garante que a quantidade seja um número positivo
        $quantidade = (int)$quantidade;
        if($quantidade > 0) {
            $itens[] = [
                'produto_id' => $produto_id,
                'quantidade' => $quantidade
            ];
        }
    }
    
    if(!empty($itens)) {
        $proforma_id = criarProforma($_SESSION['cliente_id'], $itens);
        if($proforma_id) {
            $_SESSION['mensagem_sucesso'] = "Proforma criada com sucesso! ID: $proforma_id";
            header('Location: cliente_area.php');
            exit;
        } else {
            $mensagem = "Erro ao criar proforma. Tente novamente.";
        }
    } else {
        $mensagem = "Selecione pelo menos um produto para criar a proforma.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente - <?php echo SITE_NAME; ?></title>
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
        h3 { color: var(--primary-color); margin-top: 1rem; margin-bottom: 1.5rem; border-bottom: 2px solid #ddd; padding-bottom: 0.5rem; }
        
        /* Header & Footer (Consistente com outras páginas) */
        header { background: var(--card-background); box-shadow: var(--shadow-light); position: sticky; top: 0; z-index: 1000; }
        header .container { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; }
        header h1 { color: var(--primary-color); font-size: 1.8rem; font-weight: 700; }
        nav ul { display: flex; list-style: none; gap: 2rem; }
        nav a { text-decoration: none; color: var(--text-color); font-weight: 600; padding: 0.5rem 1rem; border-radius: var(--border-radius-sm); transition: all 0.3s ease; }
        nav a:hover, nav a.active { background: var(--primary-color); color: white; }
        
        footer { background: var(--primary-color); color: white; padding: 1.5rem 0; margin-top: 4rem; text-align: center; }

        /* Estilos de Botão Genéricos */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-weight: 600;
            text-align: center;
        }
        .btn-primary { background: var(--info-color); color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: var(--success-color); color: white; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: var(--error-color); color: white; }
        .btn-danger:hover { background: var(--secondary-hover); }
        .btn-sm { font-size: 0.9rem; padding: 0.4rem 0.8rem; }
        .btn-lg { font-size: 1.2rem; padding: 0.75rem 1.5rem; }

        /* Estilos de Alerta Genéricos */
        .alert {
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Layout do Dashboard */
        .cliente-area {
            padding: 2rem 0;
        }

        .cliente-area h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .cliente-dashboard {
            display: grid;
            grid-template-columns: 2fr 3fr; /* Ajustado para dar mais espaço à criação de proforma */
            gap: 3rem; /* Aumentado o espaçamento */
            margin: 1rem 0;
        }
        
        /* Seção Minhas Proformas */
        .proforma-card {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-light);
            border-left: 5px solid var(--info-color); /* Aumentada a borda para destaque */
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease;
        }
        .proforma-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .proforma-card h4 {
            color: var(--text-color);
            margin: 0;
            font-size: 1.2rem;
        }
        
        .proforma-card.pendente { border-left-color: var(--warning-color); }
        .proforma-card.confirmada { border-left-color: var(--success-color); }
        
        .proforma-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        
        .proforma-card p {
            margin: 0.3rem 0;
            font-size: 0.95rem;
        }

        .preco {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .badge-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-pendente { background: #fff3cd; color: #856404; }
        .badge-confirmada { background: #d4edda; color: #155724; }
        
        .proforma-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        /* Seção Nova Proforma (Listagem de Produtos para Pedido) */
        .produto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .produto-card-cliente {
            background: var(--card-background);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }
        
        .produto-card-cliente h4 {
            font-size: 1.1rem;
            margin: 0.5rem 0;
            color: var(--primary-color);
        }

        .produto-card-cliente .preco {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        
        .quantidade-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 1rem;
        }
        
        .produto-imagem {
            height: 120px;
            overflow: hidden;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .produto-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .exportacao-info {
            background: #f7faff;
            border: 1px dashed #c0d8f0;
            padding: 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
        }

        .produto-descricao {
            font-size: 0.9rem;
            color: var(--text-light);
            min-height: 40px; /* Garante altura mínima */
        }
        
        .subtotal {
            text-align: right;
            margin-top: 0.5rem;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        /* Estilo para Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: var(--card-background);
            border-radius: var(--border-radius-sm);
            color: var(--text-light);
            border: 1px solid #eee;
            box-shadow: var(--shadow-light);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #bdc3c7;
        }

        /* Estilo da seção de informações adicionais */
        .info-proforma {
            background: #e8f4fd;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
            border-left: 5px solid var(--info-color);
            color: var(--text-color);
        }

        .info-proforma h4 {
            color: var(--info-color);
            margin-bottom: 1rem;
        }

        .info-proforma ul {
            list-style: none;
            padding-left: 0;
        }

        .info-proforma ul li {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        /* Responsividade */
        @media (max-width: 992px) {
            .cliente-dashboard {
                grid-template-columns: 1fr; /* Colunas empilhadas em telas menores */
                gap: 3rem;
            }
        }
        @media (max-width: 768px) {
            .proforma-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            header .container {
                flex-direction: column;
                gap: 1rem;
            }
            nav ul {
                gap: 1rem;
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
                    <li><a href="cliente_area.php" class="active">Área do Cliente</a></li>
                    <li><a href="contato.php">Contato</a></li>
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="cliente-area">
            <div class="container">
                <h2>Bem-vindo, <?php echo $_SESSION['cliente_nome']; ?>!</h2>
                
                <?php if(isset($_SESSION['mensagem_sucesso'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); ?></div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['mensagem_erro'])): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); ?></div>
                <?php endif; ?>
                
                <?php if($mensagem): ?>
                    <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <div class="cliente-dashboard">
                    <div class="proformas-section">
                        <h3><i class="fas fa-file-invoice"></i> Minhas Proformas Ativas</h3>
                        
                        <?php if(empty($proformas)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <h4>Nenhuma proforma ativa</h4>
                                <p>Você ainda não possui proformas ativas.</p>
                                <p><small>As proformas canceladas não são exibidas nesta lista.</small></p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="margin-bottom: 1rem;"><small>Mostrando <?php echo count($proformas); ?> proforma(s) ativa(s)</small></p>
                            <?php foreach($proformas as $proforma): ?>
                                <div class="proforma-card <?php echo $proforma['status']; ?>">
                                    <div class="proforma-header">
                                        <h4>Proforma #<?php echo $proforma['id']; ?></h4>
                                        <span class="badge-status badge-<?php echo $proforma['status']; ?>">
                                            <i class="fas fa-<?php echo $proforma['status'] == 'pendente' ? 'clock' : 'check'; ?>"></i>
                                            <?php echo ucfirst($proforma['status']); ?>
                                        </span>
                                    </div>
                                    <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($proforma['data_criacao'])); ?></p>
                                    <p><strong>Itens:</strong> <?php echo $proforma['total_itens']; ?></p>
                                    <p><strong>Valor Total:</strong> <span class="preco"><?php echo number_format($proforma['valor_total'], 2, ',', '.'); ?> MT</span></p>
                                    <div class="proforma-actions">
                                        <a href="exportar_proforma.php?id=<?php echo $proforma['id']; ?>" 
                                            target="_blank"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-file-pdf"></i> Exportar PDF
                                        </a>
                                        <?php if($proforma['status'] == 'pendente'): ?>
                                            <a href="cancelar_proforma.php?id=<?php echo $proforma['id']; ?>" 
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Tem certeza que deseja cancelar esta proforma? Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-times"></i> Cancelar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="nova-proforma-section">
                        <h3><i class="fas fa-cart-plus"></i> Nova Proforma</h3>
                        
                        <form method="POST" class="proforma-form">
                            <div class="produto-grid">
                                <?php foreach($produtos as $produto): ?>
                                    <div class="produto-card-cliente">
                                        
                                        <div class="produto-imagem">
                                            <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                                <img src="../uploads/<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
                                            <?php else: ?>
                                                 <div class="sem-imagem" style="font-size: 2rem; color: #ddd;"><i class="fas fa-box"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h4><?php echo $produto['nome']; ?></h4>
                                        <p class="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT</p>
                                        <p class="produto-descricao"><?php echo substr($produto['descricao'], 0, 100); ?>...</p>
                                        
                                        <?php if($produto['exportacao']): ?>
                                            <div class="exportacao-info">
                                                <p style="margin: 0; font-weight: 600;"><i class="fas fa-globe"></i> Produto de Exportação</p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="quantidade-control" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                                            <label for="quantidade_<?php echo $produto['id']; ?>">Quantidade:</label>
                                            <input type="number" 
                                                    id="quantidade_<?php echo $produto['id']; ?>" 
                                                    name="quantidade[<?php echo $produto['id']; ?>]" 
                                                    class="quantidade-input" 
                                                    min="0" 
                                                    value="0"
                                                    data-preco="<?php echo $produto['preco']; ?>"
                                                    onchange="calcularTotal()">
                                        </div>
                                        <div class="subtotal" id="subtotal_<?php echo $produto['id']; ?>">0,00 MT</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="total-section" style="background: var(--card-background); border: 1px solid #eee; padding: 1.5rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--shadow-light);">
                                <h4><i class="fas fa-receipt"></i> Resumo da Proforma</h4>
                                <div class="total-line" style="display: flex; justify-content: space-between; align-items: center; font-size: 1.2rem; margin: 1rem 0;">
                                    <strong>Total: </strong>
                                    <span id="total-geral" style="font-size: 1.6rem; color: var(--secondary-color); font-weight: 700;">0,00 MT</span>
                                </div>
                                <button type="submit" name="criar_proforma" class="btn btn-success btn-lg" style="margin-top: 1rem; width: 100%;">
                                    <i class="fas fa-file-contract"></i> Criar Proforma
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="info-proforma">
                    <h4><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                    <ul>
                        <li><i class="fas fa-tag" style="color: var(--info-color);"></i> <strong>Proformas ativas:</strong> Apenas proformas pendentes e confirmadas são exibidas.</li>
                        <li><i class="fas fa-times-circle" style="color: var(--error-color);"></i> <strong>Canceladas:</strong> Proformas canceladas são removidas automaticamente da lista.</li>
                        <li><i class="fas fa-calendar-alt" style="color: var(--warning-color);"></i> <strong>Validade:</strong> Proformas são válidas por 7 dias úteis.</li>
                        <li><i class="fas fa-factory" style="color: var(--success-color);"></i> <strong>Confirmação:</strong> Apresente a proforma na fábrica para confirmar a compra e iniciar o processamento.</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Função para formatar números em moeda Moçambicana (MT)
        function formatarMoeda(valor) {
            return valor.toLocaleString('pt-MZ', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            }).replace('.', ' ').replace(',', '.') + ' MT';
        }

        // Cálculo automático dos subtotais e total
        function calcularTotal() {
            let total = 0;
            
            document.querySelectorAll('.quantidade-input').forEach(input => {
                const quantidade = parseInt(input.value) || 0;
                // Garante que o input não é negativo
                if (quantidade < 0) {
                    input.value = 0;
                    quantidade = 0;
                }
                
                const preco = parseFloat(input.dataset.preco);
                const subtotal = quantidade * preco;
                
                const produtoIdMatch = input.name.match(/\[(\d+)\]/);
                if (produtoIdMatch) {
                    const produtoId = produtoIdMatch[1];
                    const subtotalElement = document.getElementById('subtotal_' + produtoId);
                    if (subtotalElement) {
                        subtotalElement.innerHTML = '<strong>' + formatarMoeda(subtotal) + '</strong>';
                    }
                }
                
                total += subtotal;
            });
            
            const totalGeralElement = document.getElementById('total-geral');
            if (totalGeralElement) {
                totalGeralElement.textContent = formatarMoeda(total);
            }
        }
        
        // Calcular total inicial e adicionar listeners
        document.addEventListener('DOMContentLoaded', () => {
            calcularTotal();
            document.querySelectorAll('.quantidade-input').forEach(input => {
                 input.addEventListener('change', calcularTotal);
                 input.addEventListener('input', calcularTotal);
            });
        });
    </script>
</body>
</html>