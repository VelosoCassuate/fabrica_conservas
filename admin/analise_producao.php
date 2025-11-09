<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Verificar se está logado como administrador
requireAdminAuth();

$producao_anual = getProducaoAnual();
$produtos = getProdutos();

// Obter dados de cumprimento do plano
$cumprimento_planos = getCumprimentoPlanosProducao();

// Processar cadastro de novo produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = $_POST['nome'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $exportacao = isset($_POST['exportacao']) ? 1 : 0;
    $material_embalagem = $_POST['material_embalagem'] ?? '';
    $preco_embalagem = $_POST['preco_embalagem'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    
    // Novos campos para plano de produção
    $plano_mes = $_POST['plano_mes'] ?? '';
    $plano_quantidade = $_POST['plano_quantidade'] ?? '';
    
    if (!empty($nome) && !empty($preco)) {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Inserir produto
            $query = "INSERT INTO produtos (nome, preco, exportacao, material_embalagem, preco_embalagem, descricao) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $nome);
            $stmt->bindParam(2, $preco);
            $stmt->bindParam(3, $exportacao);
            $stmt->bindParam(4, $material_embalagem);
            $stmt->bindParam(5, $preco_embalagem);
            $stmt->bindParam(6, $descricao);
            
            if ($stmt->execute()) {
                $produto_id = $db->lastInsertId();
                
                // Inserir plano de produção se os campos foram preenchidos
                if (!empty($plano_mes) && !empty($plano_quantidade)) {
                    $query_plano = "INSERT INTO plano_producao (produto_id, mes, quantidade_planeada) 
                                   VALUES (?, ?, ?)";
                    $stmt_plano = $db->prepare($query_plano);
                    $stmt_plano->bindParam(1, $produto_id);
                    $stmt_plano->bindParam(2, $plano_mes);
                    $stmt_plano->bindParam(3, $plano_quantidade);
                    
                    if (!$stmt_plano->execute()) {
                        throw new Exception("Erro ao cadastrar plano de produção.");
                    }
                }
                
                $db->commit();
                $mensagem_sucesso = "Produto cadastrado com sucesso!" . 
                                   (!empty($plano_mes) ? " Plano de produção também foi registrado." : "");
                // Atualizar lista de produtos
                $produtos = getProdutos();
                $cumprimento_planos = getCumprimentoPlanosProducao();
            } else {
                throw new Exception("Erro ao cadastrar produto.");
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $mensagem_erro = $e->getMessage();
        }
    } else {
        $mensagem_erro = "Preencha todos os campos obrigatórios.";
    }
}

// Processar registro de produção
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_producao'])) {
    $produto_id = $_POST['produto_id'] ?? '';
    $mes = $_POST['mes'] ?? '';
    $dia = $_POST['dia'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';
    
    if (!empty($produto_id) && !empty($mes) && !empty($dia) && !empty($quantidade)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO producao_real (produto_id, mes, dia, quantidade) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $produto_id);
        $stmt->bindParam(2, $mes);
        $stmt->bindParam(3, $dia);
        $stmt->bindParam(4, $quantidade);
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Produção registrada com sucesso!";
            // Atualizar dados de produção
            $producao_anual = getProducaoAnual();
            $cumprimento_planos = getCumprimentoPlanosProducao();
        } else {
            $mensagem_erro = "Erro ao registrar produção.";
        }
    } else {
        $mensagem_erro = "Preencha todos os campos para registrar a produção.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Produção - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
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
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="analise_producao.php" class="active"><i class="fas fa-chart-line"></i> Análise de Produção</a></li>
                    <li><a href="consulta_produtos.php"><i class="fas fa-boxes"></i> Consulta de Produtos</a></li>
                    <li><a href="reclamacoes.php" class="<?php echo $current_page == 'reclamacoes' ? 'active' : ''; ?>">
                            <i class="fas fa-comments"></i> Reclamações
                        </a></li>
                    <li><a href="gestao_arquivos.php"><i class="fas fa-file-upload"></i> Gestão de Arquivos</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="content-header">
                <h2><i class="fas fa-chart-line"></i> Análise de Produção</h2>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo getUserInfo()['username']; ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                </div>
            </div>

            <?php if(isset($mensagem_sucesso)): ?>
                <div class="alert alert-success"><?php echo $mensagem_sucesso; ?></div>
            <?php endif; ?>

            <?php if(isset($mensagem_erro)): ?>
                <div class="alert alert-error"><?php echo $mensagem_erro; ?></div>
            <?php endif; ?>

            <!-- Cards de Ação Rápida -->
            <div class="actions-grid">
                <a href="#" class="action-card" onclick="abrirModal('modalCadastroProduto')">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h4>Cadastrar Novo Produto</h4>
                    <p>Adicione um novo produto ao catálogo da fábrica</p>
                    <span class="btn btn-primary">Cadastrar</span>
                </a>

                <a href="#" class="action-card" onclick="abrirModal('modalRegistroProducao')">
                    <div class="action-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <h4>Registrar Produção</h4>
                    <p>Registre a produção diária dos produtos</p>
                    <span class="btn btn-success">Registrar</span>
                </a>

                <a href="consulta_produtos.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h4>Gerenciar Produtos</h4>
                    <p>Visualize e edite todos os produtos cadastrados</p>
                    <span class="btn btn-outline">Gerenciar</span>
                </a>
            </div>

            <!-- Gráficos em Grid -->
            <div class="charts-grid">
                <!-- Gráfico de Produção Anual -->
                <div class="grafico-container fade-in">
                    <div class="grafico-header">
                        <h3>Produção Anual por Produto</h3>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="graficoProducao"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Cumprimento do Plano -->
                <div class="grafico-container fade-in">
                    <div class="grafico-header">
                        <h3>Cumprimento do Plano de Produção</h3>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="graficoCumprimento"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabela de Cumprimento do Plano -->
            <div class="data-table-container fade-in">
                <div class="data-table-header">
                    <h3>Detalhes do Cumprimento do Plano</h3>
                    <div class="search-bar">
                        <input type="text" id="searchCumprimento" placeholder="Pesquisar produto..." class="search-input">
                        <button class="btn btn-primary" onclick="filtrarCumprimento()">
                            <i class="fas fa-search"></i> Pesquisar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Plano</th>
                                <th>Produzido</th>
                                <th>Percentagem</th>
                                <th>Status</th>
                                <th>Diferença</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaCumprimento">
                            <?php foreach($cumprimento_planos as $plano): 
                                $percentagem = $plano['percentagem_cumprimento'];
                                $status_class = '';
                                $status_text = '';
                                
                                if ($percentagem >= 100) {
                                    $status_class = 'badge-success';
                                    $status_text = 'Meta Atingida';
                                } elseif ($percentagem >= 80) {
                                    $status_class = 'badge-warning';
                                    $status_text = 'Próximo da Meta';
                                } else {
                                    $status_class = 'badge-danger';
                                    $status_text = 'Abaixo da Meta';
                                }
                                
                                $diferenca = $plano['quantidade_planeada'] - $plano['total_produzido'];
                            ?>
                                <tr>
                                    <td><strong><?php echo $plano['nome']; ?></strong></td>
                                    <td><?php echo number_format($plano['quantidade_planeada'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($plano['total_produzido'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo min($percentagem, 100); ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo number_format($percentagem, 1); ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($diferenca > 0): ?>
                                            <span class="text-danger">Faltam <?php echo number_format($diferenca, 0, ',', '.'); ?></span>
                                        <?php elseif ($diferenca < 0): ?>
                                            <span class="text-success">+<?php echo number_format(abs($diferenca), 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="text-success">Meta exata</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabela de Produção Detalhada -->
            <div class="data-table-container fade-in">
                <div class="data-table-header">
                    <h3>Detalhes da Produção</h3>
                    <div class="search-bar">
                        <input type="text" id="searchProducao" placeholder="Pesquisar produto..." class="search-input">
                        <button class="btn btn-primary" onclick="filtrarProducao()">
                            <i class="fas fa-search"></i> Pesquisar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Total Produzido</th>
                                <th>Maior Produção</th>
                                <th>Valor Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaProducao">
                            <?php foreach($producao_anual as $producao): 
                                $produto = getProdutoPorId($producao['id']);
                                $dia_maior = getDiaMaiorProducao($producao['id']);
                                $valor_total = ($producao['total_produzido'] ?? 0) * ($produto['preco'] ?? 0);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $producao['nome']; ?></strong>
                                        <?php if($produto['exportacao']): ?>
                                            <span class="badge badge-info">Exportação</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($producao['total_produzido'] ?? 0, 0, ',', '.'); ?> unid.</td>
                                    <td>
                                        <?php if($dia_maior): ?>
                                            <span class="badge badge-success">
                                                Dia <?php echo $dia_maior['dia']; ?>/<?php echo $dia_maior['mes']; ?>
                                            </span>
                                            <small>(<?php echo number_format($dia_maior['quantidade'], 0, ',', '.'); ?>)</small>
                                        <?php else: ?>
                                            <span class="badge badge-warning">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo number_format($valor_total, 2, ',', '.'); ?> MT</strong>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="registrarProducaoProduto(<?php echo $producao['id']; ?>, '<?php echo $producao['nome']; ?>')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Cadastro de Produto -->
    <div id="modalCadastroProduto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Cadastrar Novo Produto</h3>
                <button class="close" onclick="fecharModal('modalCadastroProduto')">&times;</button>
            </div>
            <form method="POST" class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="preco">Preço (MT) *</label>
                        <input type="number" id="preco" name="preco" step="0.01" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição do Produto</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" id="exportacao" name="exportacao" onchange="toggleEmbalagem()">
                        <span class="checkmark"></span>
                        Produto para Exportação
                    </label>
                </div>

                <div id="embalagemFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="material_embalagem">Material da Embalagem</label>
                            <input type="text" id="material_embalagem" name="material_embalagem" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="preco_embalagem">Preço da Embalagem (MT)</label>
                            <input type="number" id="preco_embalagem" name="preco_embalagem" step="0.01" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- NOVOS CAMPOS: Plano de Produção -->
                <div class="form-section">
                    <h4><i class="fas fa-calendar-alt"></i> Plano de Produção (Opcional)</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="plano_mes">Mês de Produção</label>
                            <select id="plano_mes" name="plano_mes" class="form-control">
                                <option value="">Selecione o mês</option>
                                <option value="1">Janeiro</option>
                                <option value="2">Fevereiro</option>
                                <option value="3">Março</option>
                                <option value="4">Abril</option>
                                <option value="5">Maio</option>
                                <option value="6">Junho</option>
                                <option value="7">Julho</option>
                                <option value="8">Agosto</option>
                                <option value="9">Setembro</option>
                                <option value="10">Outubro</option>
                                <option value="11">Novembro</option>
                                <option value="12">Dezembro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="plano_quantidade">Quantidade Planeada</label>
                            <input type="number" id="plano_quantidade" name="plano_quantidade" class="form-control" placeholder="Ex: 1000">
                            <small class="text-muted">Quantidade em unidades</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharModal('modalCadastroProduto')">Cancelar</button>
                    <button type="submit" name="cadastrar_produto" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cadastrar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Registro de Produção -->
    <div id="modalRegistroProducao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-industry"></i> Registrar Produção</h3>
                <button class="close" onclick="fecharModal('modalRegistroProducao')">&times;</button>
            </div>
            <form method="POST" class="modal-body">
                <input type="hidden" id="produto_id_modal" name="produto_id">
                
                <div class="form-group">
                    <label for="produto_select">Produto *</label>
                    <select id="produto_select" name="produto_id" class="form-control" required>
                        <option value="">Selecione um produto...</option>
                        <?php foreach($produtos as $produto): ?>
                            <option value="<?php echo $produto['id']; ?>">
                                <?php echo $produto['nome']; ?> - <?php echo number_format($produto['preco'], 2, ',', '.'); ?> MT
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mes">Mês *</label>
                        <select id="mes" name="mes" class="form-control" required>
                            <option value="">Selecione o mês...</option>
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo DateTime::createFromFormat('!m', $i)->format('F'); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dia">Dia *</label>
                        <select id="dia" name="dia" class="form-control" required>
                            <option value="">Selecione o dia...</option>
                            <?php for($i = 1; $i <= 31; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="quantidade">Quantidade Produzida *</label>
                    <input type="number" id="quantidade" name="quantidade" class="form-control" min="1" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharModal('modalRegistroProducao')">Cancelar</button>
                    <button type="submit" name="registrar_producao" class="btn btn-success">
                        <i class="fas fa-save"></i> Registrar Produção
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/chartjs/chart.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // Dados para o gráfico de produção anual
        const produtos = <?php echo json_encode(array_column($producao_anual, 'nome')); ?>;
        const producao = <?php echo json_encode(array_column($producao_anual, 'total_produzido')); ?>;
        
        // Dados para o gráfico de cumprimento
        const cumprimentoData = <?php echo json_encode($cumprimento_planos); ?>;
        
        // Configuração do gráfico de produção anual
        const ctxProducao = document.getElementById('graficoProducao').getContext('2d');
        new Chart(ctxProducao, {
            type: 'bar',
            data: {
                labels: produtos,
                datasets: [{
                    label: 'Produção Anual (unidades)',
                    data: producao,
                    backgroundColor: '#84c2ecff',
                    borderColor: '#3498dbff',
                    borderWidth: 2,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Produção Anual por Produto'
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

        // Configuração do gráfico de pizza para cumprimento
        const ctxCumprimento = document.getElementById('graficoCumprimento').getContext('2d');
        
        // Preparar dados para o gráfico de pizza
        const produtosCumprimento = cumprimentoData.map(item => item.nome);
        const percentagens = cumprimentoData.map(item => Math.min(item.percentagem_cumprimento, 100));
        
        // Cores para o gráfico de pizza (cores diferentes para cada produto)
        const backgroundColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
            '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
        ];

        new Chart(ctxCumprimento, {
            type: 'pie',
            data: {
                labels: produtosCumprimento,
                datasets: [{
                    data: percentagens,
                    backgroundColor: backgroundColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Percentagem de Cumprimento do Plano'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const produto = cumprimentoData[context.dataIndex];
                                const produzido = produto.total_produzido.toLocaleString('pt-BR');
                                const plano = produto.quantidade_planeada.toLocaleString('pt-BR');
                                return [
                                    `${label}: ${value.toFixed(1)}%`,
                                    `Produzido: ${produzido}`,
                                    `Plano: ${plano}`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // Funções dos modais
        function abrirModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function toggleEmbalagem() {
            const embalagemFields = document.getElementById('embalagemFields');
            const exportacaoCheckbox = document.getElementById('exportacao');
            
            if (exportacaoCheckbox.checked) {
                embalagemFields.style.display = 'block';
            } else {
                embalagemFields.style.display = 'none';
            }
        }

        function registrarProducaoProduto(produtoId, produtoNome) {
            document.getElementById('produto_id_modal').value = produtoId;
            document.getElementById('produto_select').value = produtoId;
            abrirModal('modalRegistroProducao');
        }

        function filtrarProducao() {
            const input = document.getElementById('searchProducao');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('tabelaProducao');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }

        function filtrarCumprimento() {
            const input = document.getElementById('searchCumprimento');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('tabelaCumprimento');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.classList.remove('show');
                }
            }
        }
    </script>

    <style>
    .form-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        border-left: 4px solid #007bff;
    }

    .form-section h4 {
        margin-bottom: 15px;
        color: #007bff;
        font-size: 16px;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }

    @media (max-width: 1024px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .chart-wrapper {
            height: 250px;
        }
    }

    .progress-bar-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .progress-bar {
        flex: 1;
        height: 20px;
        background: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        min-width: 100px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        transition: width 0.3s ease;
    }

    .progress-text {
        min-width: 60px;
        font-weight: bold;
        color: #333;
        font-size: 0.9em;
    }

    .badge-success { background: #28a745; color: white; }
    .badge-warning { background: #ffc107; color: black; }
    .badge-danger { background: #dc3545; color: white; }
    .badge-info { background: #17a2b8; color: white; }

    .text-success { color: #28a745; }
    .text-danger { color: #dc3545; }

    /* Responsividade das tabelas */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .data-table {
        min-width: 800px;
    }

    @media (max-width: 768px) {
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .content-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .search-bar {
            flex-direction: column;
            gap: 10px;
        }
        
        .search-input {
            width: 100%;
        }
        
        .progress-bar-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .progress-bar {
            width: 100%;
        }
    }

    /* Garantir que não há horizontal scroll */
    body {
        overflow-x: hidden;
    }
    
    .main-content {
        max-width: 100%;
        overflow-x: hidden;
    }
    </style>
</body>
</html>