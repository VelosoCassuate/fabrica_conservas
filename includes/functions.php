<?php
require_once 'database.php';

function formatarTamanhoArquivo($bytes) {
    if ($bytes == 0) return '0 B';
    
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $base = log($bytes, 1024);
    $unidade = $unidades[floor($base)];
    
    return number_format(pow(1024, $base - floor($base)), 2) . ' ' . $unidade;
}

function getProdutos() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM produtos ORDER BY nome";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function gerarChaveDeAcesso ($nome) : string {
    return mb_substr(mb_strtolower($nome), floor(mb_strlen($nome) / 2), mb_strlen($nome));
}

function registarCliente ($nome, $email) {
    $database = new Database();
    $db = $database->getConnection();

    $chave_acesso = gerarChaveDeAcesso($email);

    $query = $db->prepare("INSERT INTO clientes (nome, email, chave_acesso, ativo) VALUES (?, ?, ?, ?);");
    $query->execute([$nome, $email, $chave_acesso, 1]);  
}


function getProdutoPorId($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: verificar se o produto foi encontrado
    if (!$produto) {
        error_log("Produto não encontrado com ID: " . $id);
    }
    
    return $produto;
}

function getProdutoPorNome($nome) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM produtos WHERE nome LIKE ?";
    $stmt = $db->prepare($query);
    $nome = "%" . $nome . "%";
    $stmt->bindParam(1, $nome);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducaoAnual() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT p.id, p.nome, SUM(pr.quantidade) as total_produzido
              FROM produtos p
              LEFT JOIN producao_real pr ON p.id = pr.produto_id
              GROUP BY p.id, p.nome
              ORDER BY total_produzido DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getValorTotalProducao() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(pr.quantidade * prod.preco) as valor_total
              FROM producao_real pr
              JOIN produtos prod ON pr.produto_id = prod.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPlanoProducaoTotal() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(quantidade_planeada) as total_planeado FROM plano_producao";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProdutosExportacao() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM produtos WHERE exportacao = TRUE ORDER BY nome";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDiaMaiorProducao($produto_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT pr.dia, pr.mes, pr.quantidade
              FROM producao_real pr
              WHERE pr.produto_id = ?
              ORDER BY pr.quantidade DESC
              LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $produto_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function autenticarCliente($chave_acesso) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM clientes WHERE chave_acesso = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $chave_acesso);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function criarProforma($cliente_id, $itens) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Criar proforma
        $query = "INSERT INTO proformas (cliente_id) VALUES (?)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $cliente_id);
        $stmt->execute();
        $proforma_id = $db->lastInsertId();
        
        // Adicionar itens
        $query = "INSERT INTO proforma_itens (proforma_id, produto_id, quantidade) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        foreach($itens as $item) {
            $stmt->bindParam(1, $proforma_id);
            $stmt->bindParam(2, $item['produto_id']);
            $stmt->bindParam(3, $item['quantidade']);
            $stmt->execute();
        }
        
        $db->commit();
        return $proforma_id;
    } catch(Exception $e) {
        $db->rollBack();
        return false;
    }
}

function salvarReclamacao($nome, $email, $tipo, $mensagem) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO reclamacoes (nome, email, tipo, mensagem) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $nome);
    $stmt->bindParam(2, $email);
    $stmt->bindParam(3, $tipo);
    $stmt->bindParam(4, $mensagem);
    
    return $stmt->execute();
}
/**
 * Registra ações no sistema para auditoria
 */
function logAction($acao) {
    $usuario = $_SESSION['usuario_nome'] ?? 'Sistema';
    $data = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
    
    $log_entry = "[{$data}] [{$ip}] [{$usuario}] - {$acao}" . PHP_EOL;
    
    // Criar diretório de logs se não existir
    $log_dir = '../logs/';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    // Escrever no arquivo de log
    file_put_contents($log_dir . 'sistema.log', $log_entry, FILE_APPEND | LOCK_EX);
}
?>