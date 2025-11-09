<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdminAuth();

$produtos = getProdutos();
$mensagem_sucesso = '';
$mensagem_erro = '';

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
    
    // Processar upload de imagem
    $imagem_nome = '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem_nome = 'produto_' . time() . '.' . $extensao;
        $caminho_imagem = '../uploads/' . $imagem_nome;
        
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_imagem)) {
            $mensagem_erro = "Erro ao fazer upload da imagem.";
        }
    }
    
    if (empty($mensagem_erro)) {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Inserir produto
            $query = "INSERT INTO produtos (nome, preco, exportacao, material_embalagem, preco_embalagem, descricao, imagem) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $nome);
            $stmt->bindParam(2, $preco);
            $stmt->bindParam(3, $exportacao);
            $stmt->bindParam(4, $material_embalagem);
            $stmt->bindParam(5, $preco_embalagem);
            $stmt->bindParam(6, $descricao);
            $stmt->bindParam(7, $imagem_nome);
            
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
                $produtos = getProdutos();
            } else {
                throw new Exception("Erro ao cadastrar produto.");
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $mensagem_erro = $e->getMessage();
        }
    }
}

// Processar edição de produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_produto'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $exportacao = isset($_POST['exportacao']) ? 1 : 0;
    $material_embalagem = $_POST['material_embalagem'] ?? '';
    $preco_embalagem = $_POST['preco_embalagem'] ?? 0;
    $descricao = $_POST['descricao'];
    
    // Processar upload de imagem
    $imagem_nome = $_POST['imagem_atual'] ?? '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem_nome = 'produto_' . $id . '_' . time() . '.' . $extensao;
        $caminho_imagem = '../uploads/' . $imagem_nome;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_imagem)) {
            // Se houver imagem anterior, apagar
            if (!empty($_POST['imagem_atual']) && file_exists('../uploads/' . $_POST['imagem_atual'])) {
                unlink('../uploads/' . $_POST['imagem_atual']);
            }
        } else {
            $mensagem_erro = "Erro ao fazer upload da imagem.";
        }
    }
    
    if (empty($mensagem_erro)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE produtos SET nome = ?, preco = ?, exportacao = ?, material_embalagem = ?, 
                  preco_embalagem = ?, descricao = ?, imagem = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $nome);
        $stmt->bindParam(2, $preco);
        $stmt->bindParam(3, $exportacao);
        $stmt->bindParam(4, $material_embalagem);
        $stmt->bindParam(5, $preco_embalagem);
        $stmt->bindParam(6, $descricao);
        $stmt->bindParam(7, $imagem_nome);
        $stmt->bindParam(8, $id);
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Produto atualizado com sucesso!";
            $produtos = getProdutos();
        } else {
            $mensagem_erro = "Erro ao atualizar produto.";
        }
    }
}

// Processar exclusão de produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_produto'])) {
    $id = $_POST['id'];
    $imagem = $_POST['imagem_atual'] ?? '';
    
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Iniciar transação para garantir consistência
        $db->beginTransaction();
        
        // 1. Primeiro excluir a produção real associada
        $query_delete_producao = "DELETE FROM producao_real WHERE produto_id = ?";
        $stmt_producao = $db->prepare($query_delete_producao);
        $stmt_producao->bindParam(1, $id);
        $stmt_producao->execute();
        
        // 2. Excluir o plano de produção associado
        $query_delete_plano = "DELETE FROM plano_producao WHERE produto_id = ?";
        $stmt_plano = $db->prepare($query_delete_plano);
        $stmt_plano->bindParam(1, $id);
        $stmt_plano->execute();
        
        // 3. Verificar e tratar itens de proforma associados
        $query_check_proforma = "SELECT COUNT(*) as total FROM proforma_itens WHERE produto_id = ?";
        $stmt_check_proforma = $db->prepare($query_check_proforma);
        $stmt_check_proforma->bindParam(1, $id);
        $stmt_check_proforma->execute();
        $result_proforma = $stmt_check_proforma->fetch(PDO::FETCH_ASSOC);
        
        if ($result_proforma['total'] > 0) {
            // Excluir os itens da proforma associados a este produto
            $query_delete_proforma = "DELETE FROM proforma_itens WHERE produto_id = ?";
            $stmt_delete_proforma = $db->prepare($query_delete_proforma);
            $stmt_delete_proforma->bindParam(1, $id);
            $stmt_delete_proforma->execute();
        }
        
        // 4. Finalmente excluir o produto
        $query_delete_produto = "DELETE FROM produtos WHERE id = ?";
        $stmt_produto = $db->prepare($query_delete_produto);
        $stmt_produto->bindParam(1, $id);
        
        if ($stmt_produto->execute()) {
            // Apagar imagem se existir
            if (!empty($imagem) && file_exists('../uploads/' . $imagem)) {
                unlink('../uploads/' . $imagem);
            }
            
            // Confirmar todas as operações
            $db->commit();
            
            $mensagem_sucesso = "Produto e todos os registros associados excluídos com sucesso!";
            $produtos = getProdutos();
        } else {
            $db->rollBack();
            $mensagem_erro = "Erro ao excluir produto.";
        }
    } catch (PDOException $e) {
        // Em caso de erro, reverter todas as operações
        $db->rollBack();
        $mensagem_erro = "Erro ao excluir: " . $e->getMessage();
    }
}

$page_title = 'Consulta de Produtos';
$current_page = 'produtos';

ob_start();
?>
<!-- Ações Rápidas -->
<div class="actions-grid">
    <a href="#" class="action-card" onclick="abrirModal('modalCadastroProduto')">
        <div class="action-icon">
            <i class="fas fa-plus-circle"></i>
        </div>
        <h4>Cadastrar Novo Produto</h4>
        <p>Adicione um novo produto ao catálogo</p>
        <span class="btn btn-primary">Cadastrar</span>
    </a>
    
    <a href="analise_producao.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h4>Análise de Produção</h4>
        <p>Ver estatísticas de produção dos produtos</p>
        <span class="btn btn-success">Analisar</span>
    </a>
</div>

<!-- Tabela de Produtos -->
<div class="data-table-container fade-in">
    <div class="data-table-header">
        <h3>Catálogo de Produtos (<?php echo count($produtos); ?> produtos)</h3>
        <div class="search-bar">
            <input type="text" id="searchProdutos" placeholder="Pesquisar produtos..." class="search-input">
            <button class="btn btn-primary" onclick="filtrarProdutos()">
                <i class="fas fa-search"></i> Pesquisar
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço (MT)</th>
                    <th>Exportação</th>
                    <th>Material Embalagem</th>
                    <th>Preço Embalagem</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaProdutos">
                <?php foreach($produtos as $produto): ?>
                    <tr>
                        <td>
                            <?php if(!empty($produto['imagem']) && file_exists('../uploads/' . $produto['imagem'])): ?>
                                <img src="../uploads/<?php echo $produto['imagem']; ?>" 
                                     alt="<?php echo $produto['nome']; ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $produto['id']; ?></td>
                        <td><strong><?php echo $produto['nome']; ?></strong></td>
                        <td><?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if($produto['exportacao']): ?>
                                <span class="badge badge-success">Sim</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Não</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $produto['material_embalagem'] ?? 'N/A'; ?></td>
                        <td><?php echo $produto['preco_embalagem'] ? number_format($produto['preco_embalagem'], 2, ',', '.') : 'N/A'; ?></td>
                        <td><?php echo substr($produto['descricao'], 0, 50) . '...'; ?></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-success btn-sm" 
                                        onclick="editarProduto(<?php echo $produto['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-danger btn-sm" 
                                        onclick="excluirProduto(<?php echo $produto['id']; ?>, '<?php echo addslashes($produto['nome']); ?>', '<?php echo $produto['imagem'] ?? ''; ?>')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Cadastro de Produto -->
<div id="modalCadastroProduto" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Cadastrar Novo Produto</h3>
            <button class="close" onclick="fecharModal('modalCadastroProduto')">&times;</button>
        </div>
        <form method="POST" class="modal-body" enctype="multipart/form-data">
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
                <label for="imagem">Imagem do Produto</label>
                <input type="file" id="imagem" name="imagem" class="form-control" accept="image/*">
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

<!-- Modal Edição de Produto -->
<div id="modalEditarProduto" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Produto</h3>
            <button class="close" onclick="fecharModal('modalEditarProduto')">&times;</button>
        </div>
        <form method="POST" class="modal-body" enctype="multipart/form-data" id="formEditarProduto">
            <input type="hidden" name="id" id="editar_id">
            <input type="hidden" name="imagem_atual" id="editar_imagem_atual">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="editar_nome">Nome do Produto *</label>
                    <input type="text" id="editar_nome" name="nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editar_preco">Preço (MT) *</label>
                    <input type="number" id="editar_preco" name="preco" step="0.01" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="editar_descricao">Descrição do Produto</label>
                <textarea id="editar_descricao" name="descricao" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="editar_imagem">Imagem do Produto</label>
                <div class="imagem-atual mb-2" id="container_imagem_atual"></div>
                <input type="file" id="editar_imagem" name="imagem" class="form-control" accept="image/*">
                <small class="text-muted">Deixe em branco para manter a imagem atual</small>
            </div>

            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" id="editar_exportacao" name="exportacao" onchange="toggleEmbalagemEdicao()">
                    <span class="checkmark"></span>
                    Produto para Exportação
                </label>
            </div>

            <div id="embalagemFieldsEdicao" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editar_material_embalagem">Material da Embalagem</label>
                        <input type="text" id="editar_material_embalagem" name="material_embalagem" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="editar_preco_embalagem">Preço da Embalagem (MT)</label>
                        <input type="number" id="editar_preco_embalagem" name="preco_embalagem" step="0.01" class="form-control">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="fecharModal('modalEditarProduto')">Cancelar</button>
                <button type="submit" name="editar_produto" class="btn btn-success">
                    <i class="fas fa-save"></i> Atualizar Produto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmação Exclusão -->
<div id="modalExcluirProduto" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <button class="close" onclick="fecharModal('modalExcluirProduto')">&times;</button>
        </div>
        <form method="POST" class="modal-body" id="formExcluirProduto">
            <input type="hidden" name="id" id="excluir_id">
            <input type="hidden" name="imagem_atual" id="excluir_imagem_atual">
            
            <p>Tem certeza que deseja excluir o produto <strong id="excluir_nome"></strong>?</p>
            <p class="text-danger"><strong>Atenção:</strong> Esta ação não pode ser desfeita!</p>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="fecharModal('modalExcluirProduto')">Cancelar</button>
                <button type="submit" name="excluir_produto" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Dados dos produtos em JavaScript
const produtosData = <?php echo json_encode($produtos); ?>;

// Funções JavaScript
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

function toggleEmbalagemEdicao() {
    const embalagemFields = document.getElementById('embalagemFieldsEdicao');
    const exportacaoCheckbox = document.getElementById('editar_exportacao');
    
    if (exportacaoCheckbox.checked) {
        embalagemFields.style.display = 'block';
    } else {
        embalagemFields.style.display = 'none';
    }
}

function editarProduto(id) {
    console.log('Editando produto ID:', id);
    console.log('Produtos disponíveis:', produtosData);
    
    const produto = produtosData.find(p => p.id == id);
    
    if (produto) {
        console.log('Produto encontrado:', produto);
        
        document.getElementById('editar_id').value = produto.id;
        document.getElementById('editar_nome').value = produto.nome;
        document.getElementById('editar_preco').value = produto.preco;
        document.getElementById('editar_descricao').value = produto.descricao;
        document.getElementById('editar_exportacao').checked = produto.exportacao == 1 || produto.exportacao === '1';
        document.getElementById('editar_material_embalagem').value = produto.material_embalagem || '';
        document.getElementById('editar_preco_embalagem').value = produto.preco_embalagem || '';
        document.getElementById('editar_imagem_atual').value = produto.imagem || '';
        
        // Mostrar imagem atual
        const containerImagem = document.getElementById('container_imagem_atual');
        if (produto.imagem) {
            containerImagem.innerHTML = `
                <img src="../uploads/${produto.imagem}" alt="${produto.nome}" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 2px solid #ddd;">
                <br><small>Imagem atual</small>
            `;
        } else {
            containerImagem.innerHTML = '<small>Nenhuma imagem cadastrada</small>';
        }
        
        toggleEmbalagemEdicao();
        abrirModal('modalEditarProduto');
    } else {
        console.error('Produto não encontrado com ID:', id);
        alert('Produto não encontrado!');
    }
}

function excluirProduto(id, nome, imagem) {
    document.getElementById('excluir_id').value = id;
    document.getElementById('excluir_nome').textContent = nome;
    document.getElementById('excluir_imagem_atual').value = imagem;
    abrirModal('modalExcluirProduto');
}

function filtrarProdutos() {
    const input = document.getElementById('searchProdutos');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('tabelaProdutos');
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

// Pesquisa em tempo real
document.getElementById('searchProdutos').addEventListener('input', filtrarProdutos);

// Debug: verificar se os produtos foram carregados
console.log('Produtos carregados:', produtosData);
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
</style>
<?php
$content = ob_get_clean();
include 'template.php';
?>