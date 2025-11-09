<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdminAuth();

// Buscar todas as reclamações
$database = new Database();
$db = $database->getConnection();

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $query = "DELETE FROM reclamacoes WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = "Mensagem excluída com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao excluir mensagem.";
    }
    header('Location: reclamacoes.php');
    exit;
}

$query = "SELECT * FROM reclamacoes ORDER BY data_envio DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$reclamacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Gestão de Reclamações';
$current_page = 'reclamacoes';

ob_start();
?>
<!-- Ações Rápidas -->
<div class="actions-grid">
    <a href="index.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <h4>Voltar ao Dashboard</h4>
        <p>Retornar à página principal</p>
        <span class="btn btn-primary">Voltar</span>
    </a>
</div>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-number"><?php echo count($reclamacoes); ?></div>
        <div class="stat-label">Total de Mensagens</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-number">
            <?php
            $reclamacoes_count = 0;
            foreach ($reclamacoes as $r) {
                if ($r['tipo'] == 'reclamacao') $reclamacoes_count++;
            }
            echo $reclamacoes_count;
            ?>
        </div>
        <div class="stat-label">Reclamações</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="stat-number">
            <?php
            $sugestoes_count = 0;
            foreach ($reclamacoes as $r) {
                if ($r['tipo'] == 'sugestao') $sugestoes_count++;
            }
            echo $sugestoes_count;
            ?>
        </div>
        <div class="stat-label">Sugestões</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number">
            <?php
            $hoje_count = 0;
            $hoje = date('Y-m-d');
            foreach ($reclamacoes as $r) {
                if (date('Y-m-d', strtotime($r['data_envio'])) == $hoje) {
                    $hoje_count++;
                }
            }
            echo $hoje_count;
            ?>
        </div>
        <div class="stat-label">Hoje</div>
    </div>
</div>

<!-- Lista de Reclamações -->
<div class="data-table-container fade-in">
    <div class="data-table-header">
        <h3>Mensagens dos Clientes (<?php echo count($reclamacoes); ?>)</h3>
        <div class="search-bar">
            <input type="text" id="searchReclamacoes" placeholder="Pesquisar mensagens..." class="search-input">
            <button class="btn btn-primary" onclick="filtrarReclamacoes()">
                <i class="fas fa-search"></i> Pesquisar
            </button>
        </div>
    </div>

    <?php if (empty($reclamacoes)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Nenhuma mensagem encontrada.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="80">Tipo</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Mensagem</th>
                    <th width="150">Data</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaReclamacoes">
                <?php foreach ($reclamacoes as $reclamacao): ?>
                    <tr>
                        <td>
                            <?php if ($reclamacao['tipo'] == 'reclamacao'): ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-circle"></i> Reclamação
                                </span>
                            <?php else: ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-lightbulb"></i> Sugestão
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($reclamacao['nome']); ?></strong>
                        </td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($reclamacao['email']); ?>">
                                <?php echo htmlspecialchars($reclamacao['email']); ?>
                            </a>
                        </td>
                        <td>
                            <div style="max-height: 60px; overflow: hidden;">
                                <?php echo htmlspecialchars(substr($reclamacao['mensagem'], 0, 100)); ?>
                                <?php if (strlen($reclamacao['mensagem']) > 100): ?>...<?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($reclamacao['data_envio'])); ?></small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm"
                                    onclick="verMensagem(
                                            '<?php echo addslashes($reclamacao['nome']); ?>',
                                            '<?php echo addslashes($reclamacao['email']); ?>',
                                            '<?php echo $reclamacao['tipo']; ?>',
                                            '<?php echo addslashes($reclamacao['mensagem']); ?>',
                                            '<?php echo date('d/m/Y H:i', strtotime($reclamacao['data_envio'])); ?>'
                                        )">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <a href="reclamacoes.php?excluir=<?php echo $reclamacao['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tem certeza que deseja excluir a mensagem de <?php echo addslashes($reclamacao['nome']); ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal Ver Mensagem Completa -->
<div id="modalVerMensagem" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-envelope"></i> Mensagem Completa</h3>
            <button class="close" onclick="fecharModal('modalVerMensagem')">&times;</button>
        </div>
        <div class="modal-body" id="conteudoMensagem">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="fecharModal('modalVerMensagem')">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    function filtrarReclamacoes() {
        const input = document.getElementById('searchReclamacoes');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('tabelaReclamacoes');
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

    function verMensagem(nome, email, tipo, mensagem, data) {
        const tipoTexto = tipo == 'reclamacao' ? 'Reclamação' : 'Sugestão';
        const conteudo = `
        <div style="margin-bottom: 1rem;">
            <p><strong>Nome:</strong> ${nome}</p>
            <p><strong>Email:</strong> <a href="mailto:${email}">${email}</a></p>
            <p><strong>Tipo:</strong> ${tipoTexto}</p>
            <p><strong>Data:</strong> ${data}</p>
        </div>
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
            <strong>Mensagem:</strong>
            <p style="margin-top: 0.5rem; white-space: pre-wrap;">${mensagem}</p>
        </div>
    `;
        document.getElementById('conteudoMensagem').innerHTML = conteudo;
        abrirModal('modalVerMensagem');
    }

    function abrirModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    function fecharModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // Pesquisa em tempo real
    document.getElementById('searchReclamacoes').addEventListener('keyup', filtrarReclamacoes);

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
<?php
$content = ob_get_clean();
include 'template.php';
?>