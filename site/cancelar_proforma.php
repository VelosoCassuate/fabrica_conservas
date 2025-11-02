<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Iniciar sessão apenas se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['cliente_id'])) {
    header('Location: cliente_login.php');
    exit;
}

$proforma_id = $_GET['id'] ?? 0;

if($proforma_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se a proforma pertence ao cliente e está pendente
    $query = "SELECT * FROM proformas WHERE id = ? AND cliente_id = ? AND status = 'pendente'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $proforma_id);
    $stmt->bindParam(2, $_SESSION['cliente_id']);
    $stmt->execute();
    $proforma = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($proforma) {
        // Cancelar a proforma
        $query_update = "UPDATE proformas SET status = 'cancelada' WHERE id = ?";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bindParam(1, $proforma_id);
        
        if($stmt_update->execute()) {
            $_SESSION['mensagem_sucesso'] = "Proforma #$proforma_id cancelada com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao cancelar proforma.";
        }
    } else {
        $_SESSION['mensagem_erro'] = "Proforma não encontrada ou não pode ser cancelada.";
    }
} else {
    $_SESSION['mensagem_erro'] = "ID da proforma não especificado.";
}

header('Location: cliente_area.php');
exit;
?>