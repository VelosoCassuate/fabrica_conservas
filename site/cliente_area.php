<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

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
    <link rel="stylesheet" href="css/site.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .cliente-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .proforma-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
            margin-bottom: 1rem;
        }
        
        .proforma-card.pendente {
            border-left-color: #f39c12;
        }
        
        .proforma-card.confirmada {
            border-left-color: #27ae60;
        }
        
        .produto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .produto-card-cliente {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .produto-card-cliente:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .quantidade-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .badge-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-confirmada {
            background: #d4edda;
            color: #155724;
        }
        
        .proforma-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .proforma-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        @media (max-width: 768px) {
            .cliente-dashboard {
                grid-template-columns: 1fr;
            }
            
            .proforma-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
                    <div class="alert alert-success"><?php echo $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); ?></div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['mensagem_erro'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); ?></div>
                <?php endif; ?>
                
                <?php if($mensagem): ?>
                    <div class="alert alert-error"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <div class="cliente-dashboard">
                    <!-- Minhas Proformas Ativas -->
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
                            <p class="text-muted"><small>Mostrando <?php echo count($proformas); ?> proforma(s) ativa(s)</small></p>
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
                    
                    <!-- Nova Proforma -->
                    <div class="nova-proforma-section">
                        <h3><i class="fas fa-cart-plus"></i> Nova Proforma</h3>
                        
                        <form method="POST" class="proforma-form">
                            <div class="produto-grid">
                                <?php foreach($produtos as $produto): ?>
                                    <div class="produto-card-cliente">
                                        <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                            <div class="produto-imagem">
                                                <img src="../uploads/<?php echo $produto['imagem']; ?>" 
                                                     alt="<?php echo $produto['nome']; ?>">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h4><?php echo $produto['nome']; ?></h4>
                                        <p class="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT</p>
                                        <p class="produto-descricao"><?php echo substr($produto['descricao'], 0, 100); ?>...</p>
                                        
                                        <?php if($produto['exportacao']): ?>
                                            <div class="exportacao-info">
                                                <p><strong><i class="fas fa-globe"></i> Produto de Exportação</strong></p>
                                                <p><strong>Embalagem:</strong> <?php echo $produto['material_embalagem']; ?></p>
                                                <?php if($produto['preco_embalagem'] > 0): ?>
                                                    <p><strong>Preço embalagem:</strong> <?php echo number_format($produto['preco_embalagem'], 2, ',', '.'); ?> MT</p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="quantidade-control" style="padding: 10px 0;">
                                            <label for="quantidade_<?php echo $produto['id']; ?>"><strong>Quantidade:</strong></label>
                                            <input type="number" 
                                                   id="quantidade_<?php echo $produto['id']; ?>" 
                                                   name="quantidade[<?php echo $produto['id']; ?>]" 
                                                   class="quantidade-input" 
                                                   min="0" 
                                                   value="0"
                                                   data-preco="<?php echo $produto['preco']; ?>"
                                                   onchange="calcularTotal()">
                                        </div>
                                        <div class="subtotal" id="subtotal_<?php echo $produto['id']; ?>"><strong>0,00 MT</strong></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="total-section" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
                                <h4><i class="fas fa-receipt"></i> Resumo da Proforma</h4>
                                <div class="total-line" style="font-size: 1.2rem;">
                                    <strong>Total: </strong>
                                    <span id="total-geral" style="font-size: 1.5rem; color: #e74c3c;">0,00 MT</span>
                                </div>
                                <button type="submit" name="criar_proforma" class="btn btn-success btn-lg" style="margin-top: 1rem; width: 100%;">
                                    <i class="fas fa-file-contract"></i> Criar Proforma
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="info-proforma" style="background: #e8f4fd; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
                    <h4><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                    <ul>
                        <li><strong>Proformas ativas:</strong> Apenas proformas pendentes e confirmadas são exibidas</li>
                        <li><strong>Canceladas:</strong> Proformas canceladas são removidas automaticamente da lista</li>
                        <li><strong>Validade:</strong> Proformas são válidas por 7 dias úteis</li>
                        <li><strong>Confirmação:</strong> Apresente a proforma na fábrica para confirmar a compra</li>
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
        // Cálculo automático dos subtotais e total
        function calcularTotal() {
            let total = 0;
            
            document.querySelectorAll('.quantidade-input').forEach(input => {
                const quantidade = parseInt(input.value) || 0;
                const preco = parseFloat(input.dataset.preco);
                const subtotal = quantidade * preco;
                
                const produtoId = input.name.match(/\[(\d+)\]/)[1];
                const subtotalElement = document.getElementById('subtotal_' + prodottoId);
                if (subtotalElement) {
                    subtotalElement.innerHTML = '<strong>' + subtotal.toFixed(2).replace('.', ',') + ' MT</strong>';
                }
                
                total += subtotal;
            });
            
            const totalGeralElement = document.getElementById('total-geral');
            if (totalGeralElement) {
                totalGeralElement.textContent = total.toFixed(2).replace('.', ',') + ' MT';
            }
        }
        
        // Calcular total inicial
        document.addEventListener('DOMContentLoaded', calcularTotal);
    </script>
</body>
</html>