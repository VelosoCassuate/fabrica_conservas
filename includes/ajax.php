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
    // No switch case do ajax.php, adicione:
    case 'get_reclamacao':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $query = "SELECT * FROM reclamacoes WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $reclamacao = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($reclamacao);
        }
        break;

    case 'excluir_reclamacao':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $query = "DELETE FROM reclamacoes WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Ação não reconhecida']);
        break;
}
