<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdminAuth();

// Buscar dados reais de produção
$producao_mensal = [];
for ($mes = 1; $mes <= 6; $mes++) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COALESCE(SUM(quantidade), 0) as total 
              FROM producao_real 
              WHERE mes = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $mes);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $producao_mensal[] = $result['total'];
}

$page_title = 'Dashboard';
$current_page = 'dashboard';

ob_start();
?>
<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card fade-in">
        <div class="stat-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-number"><?php echo count(getProdutos()); ?></div>
        <div class="stat-label">Total de Produtos</div>
    </div>
    
    <div class="stat-card fade-in">
        <div class="stat-icon">
            <i class="fas fa-globe"></i>
        </div>
        <div class="stat-number"><?php echo count(getProdutosExportacao()); ?></div>
        <div class="stat-label">Produtos para Exportação</div>
    </div>
    
    <div class="stat-card fade-in">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-number">
            <?php 
            $valor_total = getValorTotalProducao();
            echo number_format($valor_total['valor_total'] ?? 0, 2, ',', '.'); 
            ?> MT
        </div>
        <div class="stat-label">Valor Total da Produção</div>
    </div>
    
    <div class="stat-card fade-in">
        <div class="stat-icon">
            <i class="fas fa-target"></i>
        </div>
        <div class="stat-number">
            <?php 
            $plano_total = getPlanoProducaoTotal();
            echo number_format($plano_total['total_planeado'] ?? 0, 0, ',', '.'); 
            ?>
        </div>
        <div class="stat-label">Plano de Produção Total</div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="actions-grid">
    <a href="analise_producao.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h4>Análise de Produção</h4>
        <p>Ver relatórios detalhados de produção e estatísticas</p>
        <span class="btn btn-primary">Acessar</span>
    </a>
    
    <a href="consulta_produtos.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <h4>Consulta de Produtos</h4>
        <p>Gerenciar catálogo completo de produtos</p>
        <span class="btn btn-success">Gerenciar</span>
    </a>
    
    <a href="gestao_arquivos.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-file-upload"></i>
        </div>
        <h4>Gestão de Arquivos</h4>
        <p>Upload e organização de documentos e imagens</p>
        <span class="btn btn-warning">Gerenciar</span>
    </a>
</div>

<!-- Gráfico com Dados Reais -->
<div class="grafico-container fade-in">
    <div class="grafico-header">
        <h3>Produção Real (Últimos 6 Meses)</h3>
        <div class="btn-group">
            <button class="btn btn-outline btn-sm" onclick="exportarDados('dashboard')">
                <i class="fas fa-download"></i> Exportar PDF
            </button>
        </div>
    </div>
    <canvas id="graficoDashboard" width="400" height="200"></canvas>
</div>

<script src="../assets/chartjs/chart.min.js"></script>
<script>
    // Gráfico com dados reais do PHP
    const ctx = document.getElementById('graficoDashboard').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'],
            datasets: [{
                label: 'Produção Mensal (unidades)',
                data: <?php echo json_encode($producao_mensal); ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Produção Real por Mês'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    function exportarDados(tipo) {
        window.open('exportar_pdf.php?tipo=' + tipo, '_blank');
    }
</script>
<?php
$content = ob_get_clean();
include 'template.php';
?>