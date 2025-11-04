<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireAdminAuth();

// Diretórios
$upload_dir = '../uploads/';
$export_dir = '../exports/';

// Verificar parâmetros
if (!isset($_GET['nome']) || !isset($_GET['tipo'])) {
    header('Location: gestao_arquivos.php?erro=Parâmetros inválidos');
    exit();
}

$nome_arquivo = basename($_GET['nome']);
$tipo = $_GET['tipo'];

// Determinar o diretório baseado no tipo
if ($tipo === 'upload') {
    $diretorio = $upload_dir;
} elseif ($tipo === 'export') {
    $diretorio = $export_dir;
} else {
    header('Location: gestao_arquivos.php?erro=Tipo de arquivo inválido');
    exit();
}

$caminho_completo = $diretorio . $nome_arquivo;

// Verificar se o arquivo existe
if (!file_exists($caminho_completo)) {
    header('Location: gestao_arquivos.php?erro=Arquivo não encontrado');
    exit();
}

// Verificar se é um arquivo válido (não diretório)
if (!is_file($caminho_completo)) {
    header('Location: gestao_arquivos.php?erro=Item não é um arquivo válido');
    exit();
}

// Tentar excluir o arquivo
if (unlink($caminho_completo)) {
    // Log da ação
    logAction("Arquivo excluído: {$nome_arquivo} ({$tipo})");
    
    header('Location: gestao_arquivos.php?sucesso=Arquivo excluído com sucesso');
    exit();
} else {
    header('Location: gestao_arquivos.php?erro=Erro ao excluir arquivo');
    exit();
}
?>