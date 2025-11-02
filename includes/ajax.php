<?php
require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_produto':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $produto = getProdutoPorId($id);
            echo json_encode($produto);
        } else {
            echo json_encode(['error' => 'ID não fornecido']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Ação não reconhecida']);
        break;
}
?>