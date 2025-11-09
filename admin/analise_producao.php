<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Verificar se está logado como administrador
requireAdminAuth();

$producao_anual = getProducaoAnual();
$produtos = getProdutos();

// Processar cadastro de novo produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = $_POST['nome'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $exportacao = isset($_POST['exportacao']) ? 1 : 0;
    $material_embalagem = $_POST['material_embalagem'] ?? '';
    $preco_embalagem = $_POST['preco_embalagem'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    
    if (!empty($nome) && !empty($preco)) {
        $database = new Database();
        $db = $database->getConnection();
        
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
            $mensagem_sucesso = "Produto cadastrado com sucesso!";
            // Atualizar lista de produtos
            $produtos = getProdutos();
        } else {
            $mensagem_erro = "Erro ao cadastrar produto.";
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
        } else {
            $mensagem_erro = "Erro ao registrar produção.";
        }
    } else {
        $mensagem_erro = "Preencha todos osed campos para registrar a produção.";
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

            <!-- Gráfico de Produção -->
            <div class="grafico-container fade-in">
                <div class="grafico-header">
                    <h3>Produção Anual por Produto</h3>
                    <!-- <div class="btn-group">
                        <button class="btn btn-outline btn-sm" onclick="exportarDados('grafico')">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div> -->
                </div>
                <canvas id="graficoProducao" width="400" height="200"></canvas>
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
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Total Produzido</th>
                            <th>Dia de Maior Produção</th>
                            <th>Valor Total (MT)</th>
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
                                <td><?php echo number_format($producao['total_produzido'] ?? 0, 0, ',', '.'); ?> unidades</td>
                                <td>
                                    <?php if($dia_maior): ?>
                                        <span class="badge badge-success">
                                            Dia <?php echo $dia_maior['dia']; ?>/<?php echo $dia_maior['mes']; ?>
                                        </span>
                                        (<?php echo number_format($dia_maior['quantidade'], 0, ',', '.'); ?> unidades)
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
                                        <i class="fas fa-plus"></i> Produção
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                                <?php $meses = ['jan','feve'] ?>
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
        // Dados para o gráfico
        const produtos = <?php echo json_encode(array_column($producao_anual, 'nome')); ?>;
        const producao = <?php echo json_encode(array_column($producao_anual, 'total_produzido')); ?>;
        
        // Configuração do gráfico
        const ctx = document.getElementById('graficoProducao').getContext('2d');
        new Chart(ctx, {
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

        function exportarDados(tipo) {
            alert('Funcionalidade de exportação será implementada aqui.');
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
</body>
</html>