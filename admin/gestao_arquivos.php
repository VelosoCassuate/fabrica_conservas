<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdminAuth();

// Diretório de uploads
$upload_dir = '../uploads/';
$export_dir = '../exports/';

// Criar diretórios se não existirem
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
if (!file_exists($export_dir)) mkdir($export_dir, 0777, true);

// Inicializar mensagens
$mensagem_sucesso = '';
$mensagem_erro = '';

// Processar upload de arquivo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['arquivo'])) {
        $arquivo = $_FILES['arquivo'];
        $nome_arquivo = basename($arquivo['name']);
        $caminho_completo = $upload_dir . $nome_arquivo;
        
        // Verificar se é um tipo de arquivo permitido
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx'];
        $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $tipos_permitidos)) {
            $mensagem_erro = "Tipo de arquivo não permitido. Use: " . implode(', ', $tipos_permitidos);
        } elseif (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            $mensagem_sucesso = "Arquivo '$nome_arquivo' enviado com sucesso!";
        } else {
            $mensagem_erro = "Erro ao enviar arquivo.";
        }
    }
    
    // Exportar produção para PDF simples
    if (isset($_POST['exportar_producao'])) {
        $producao_anual = getProducaoAnual();
        
        // Gerar conteúdo HTML para o PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Relatório de Produção - ' . EMPRESA_NOME . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { background-color: #e8f4fd; font-weight: bold; }
                .header { margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Relatório de Produção</h1>
                <p><strong>Empresa:</strong> ' . EMPRESA_NOME . '</p>
                <p><strong>Data de emissão:</strong> ' . date('d/m/Y H:i') . '</p>
            </div>
            
            <table>
                <tr>
                    <th>Produto</th>
                    <th>Preço (MT)</th>
                    <th>Exportação</th>
                    <th>Produção Total</th>
                    <th>Valor Total (MT)</th>
                </tr>';
        
        $total_geral = 0;
        foreach($producao_anual as $producao) {
            $produto = getProdutoPorId($producao['id']);
            $valor_total = ($producao['total_produzido'] ?? 0) * ($produto['preco'] ?? 0);
            $total_geral += $valor_total;
            
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($produto['nome']) . '</td>
                    <td>' . number_format($produto['preco'], 2, ',', '.') . '</td>
                    <td>' . ($produto['exportacao'] ? 'Sim' : 'Não') . '</td>
                    <td>' . number_format($producao['total_produzido'] ?? 0, 0, ',', '.') . '</td>
                    <td>' . number_format($valor_total, 2, ',', '.') . '</td>
                </tr>';
        }
        
        $html .= '
                <tr class="total">
                    <td colspan="4"><strong>TOTAL GERAL</strong></td>
                    <td><strong>' . number_format($total_geral, 2, ',', '.') . ' MT</strong></td>
                </tr>
            </table>
            
            <div style="margin-top: 30px; font-size: 12px; color: #666;">
                <p>Relatório gerado automaticamente pelo sistema ' . EMPRESA_NOME . '</p>
            </div>
        </body>
        </html>';
        
        $filename = 'relatorio_producao_' . date('Y-m-d_H-i') . '.html';
        $filepath = $export_dir . $filename;
        
        if (file_put_contents($filepath, $html)) {
            $mensagem_sucesso = "Relatório exportado com sucesso: " . $filename;
        } else {
            $mensagem_erro = "Erro ao gerar relatório.";
        }
    }
    
    // Exportar para CSV (alternativa ao PDF)
    if (isset($_POST['exportar_csv'])) {
        $producao_anual = getProducaoAnual();
        
        $csv_content = "Produto;Preço (MT);Exportação;Produção Total;Valor Total (MT)\n";
        
        $total_geral = 0;
        foreach($producao_anual as $producao) {
            $produto = getProdutoPorId($producao['id']);
            $valor_total = ($producao['total_produzido'] ?? 0) * ($produto['preco'] ?? 0);
            $total_geral += $valor_total;
            
            $csv_content .= '"' . $produto['nome'] . '";' .
                           number_format($produto['preco'], 2, ',', '.') . ';' .
                           ($produto['exportacao'] ? 'Sim' : 'Não') . ';' .
                           number_format($producao['total_produzido'] ?? 0, 0, ',', '.') . ';' .
                           number_format($valor_total, 2, ',', '.') . "\n";
        }
        
        $csv_content .= '"TOTAL GERAL";"";"";"";' . number_format($total_geral, 2, ',', '.') . "\n";
        
        $filename = 'relatorio_producao_' . date('Y-m-d_H-i') . '.csv';
        $filepath = $export_dir . $filename;
        
        if (file_put_contents($filepath, $csv_content)) {
            $mensagem_sucesso = "Relatório CSV exportado com sucesso: " . $filename;
        } else {
            $mensagem_erro = "Erro ao gerar relatório CSV.";
        }
    }
}

// Listar arquivos existentes
$arquivos = [];
if (file_exists($upload_dir)) {
    $itens = scandir($upload_dir);
    foreach ($itens as $item) {
        if ($item != '.' && $item != '..') {
            $caminho = $upload_dir . $item;
            $arquivos[] = [
                'nome' => $item,
                'tamanho' => filesize($caminho),
                'data_modificacao' => filemtime($caminho),
                'tipo' => 'upload',
                'caminho' => $upload_dir
            ];
        }
    }
}

// Listar arquivos exportados
if (file_exists($export_dir)) {
    $itens = scandir($export_dir);
    foreach ($itens as $item) {
        if ($item != '.' && $item != '..') {
            $caminho = $export_dir . $item;
            $arquivos[] = [
                'nome' => $item,
                'tamanho' => filesize($caminho),
                'data_modificacao' => filemtime($caminho),
                'tipo' => 'export',
                'caminho' => $export_dir
            ];
        }
    }
}

// Ordenar por data
usort($arquivos, function($a, $b) {
    return $b['data_modificacao'] - $a['data_modificacao'];
});

$page_title = 'Gestão de Arquivos';
$current_page = 'arquivos';

ob_start();
?>
<!-- Ações Rápidas -->
<div class="actions-grid">
    <a href="consulta_produtos.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <h4>Gerenciar Produtos</h4>
        <p>Editar produtos e upload de imagens</p>
        <span class="btn btn-primary">Gerenciar</span>
    </a>
    
    <form method="POST" class="action-card" style="cursor: pointer; border: 2px dashed #3498db;">
        <div class="action-icon">
            <i class="fas fa-file-export"></i>
        </div>
        <h4>Exportar Relatórios</h4>
        <p>Gerar relatórios de produção</p>
        <div class="btn-group" style="margin-top: 10px;">
            <button type="submit" name="exportar_producao" class="btn btn-success btn-sm">
                <i class="fas fa-file-html"></i> HTML
            </button>
            <button type="submit" name="exportar_csv" class="btn btn-info btn-sm">
                <i class="fas fa-file-csv"></i> CSV
            </button>
        </div>
    </form>
</div>

<!-- Upload Section -->
<div class="form-container fade-in">
    <h3><i class="fas fa-upload"></i> Upload de Arquivos</h3>
    <form method="POST" enctype="multipart/form-data" class="upload-form">
        <div class="form-group">
            <label for="arquivo">Selecionar arquivo:</label>
            <input type="file" id="arquivo" name="arquivo" class="form-control" required>
            <small class="text-muted">Tipos permitidos: JPG, PNG, GIF, PDF, TXT, DOC, DOCX (Max: 10MB)</small>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Fazer Upload
        </button>
    </form>
</div>

<!-- Lista de Arquivos -->
<div class="data-table-container fade-in">
    <div class="data-table-header">
        <h3>Arquivos Existentes (<?php echo count($arquivos); ?>)</h3>
        <div class="search-bar">
            <input type="text" id="searchArquivos" placeholder="Pesquisar arquivos..." class="search-input">
            <button class="btn btn-primary" onclick="filtrarArquivos()">
                <i class="fas fa-search"></i> Pesquisar
            </button>
        </div>
    </div>
    
    <?php if(empty($arquivos)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Nenhum arquivo encontrado.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome do Arquivo</th>
                    <th>Tipo</th>
                    <th>Tamanho</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaArquivos">
                <?php foreach($arquivos as $arquivo): 
                    $extensao = strtolower(pathinfo($arquivo['nome'], PATHINFO_EXTENSION));
                    $is_html = $extensao == 'html';
                    $is_csv = $extensao == 'csv';
                    $is_pdf = $extensao == 'pdf';
                    $is_imagem = in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $is_documento = in_array($extensao, ['txt', 'doc', 'docx']);
                ?>
                    <tr>
                        <td>
                            <i class="fas fa-<?php 
                                if ($is_html) echo 'file-code text-warning';
                                elseif ($is_csv) echo 'file-csv text-success';
                                elseif ($is_pdf) echo 'file-pdf text-danger';
                                elseif ($is_imagem) echo 'image text-primary';
                                elseif ($is_documento) echo 'file-alt text-info';
                                else echo 'file text-secondary';
                            ?>"></i>
                            <?php echo htmlspecialchars($arquivo['nome']); ?>
                            <?php if($arquivo['tipo'] == 'export'): ?>
                                <span class="badge badge-success">Exportado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php 
                                if ($is_html) echo 'warning';
                                elseif ($is_csv) echo 'success';
                                elseif ($is_pdf) echo 'danger';
                                elseif ($is_imagem) echo 'primary';
                                elseif ($is_documento) echo 'info';
                                else echo 'secondary';
                            ?>">
                                <?php echo strtoupper($extensao); ?>
                            </span>
                        </td>
                        <td><?php echo formatarTamanhoArquivo($arquivo['tamanho']); ?></td>
                        <td><?php echo date('d/m/Y H:i', $arquivo['data_modificacao']); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?php echo $arquivo['caminho'] . $arquivo['nome']; ?>" 
                                   download 
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i>
                                </a>
                                <?php if($is_imagem || $is_pdf || $is_html): ?>
                                <a href="<?php echo $arquivo['caminho'] . $arquivo['nome']; ?>" 
                                   target="_blank"
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <button onclick="excluirArquivo('<?php echo $arquivo['nome']; ?>', '<?php echo $arquivo['tipo']; ?>')" 
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function filtrarArquivos() {
    const input = document.getElementById('searchArquivos');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('tabelaArquivos');
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

function excluirArquivo(nome, tipo) {
    if (confirm(`Tem certeza que deseja excluir o arquivo "${nome}"?`)) {
        window.location.href = `excluir_arquivo.php?nome=${encodeURIComponent(nome)}&tipo=${tipo}`;
    }
}

// Pesquisa em tempo real
document.getElementById('searchArquivos').addEventListener('keyup', filtrarArquivos);
</script>
<?php
$content = ob_get_clean();
include 'template.php';
?>