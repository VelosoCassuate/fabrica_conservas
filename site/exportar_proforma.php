<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();

if(!isset($_SESSION['cliente_id'])) {
    header('Location: cliente_login.php');
    exit;
}

$proforma_id = $_GET['id'] ?? 0;

if($proforma_id) {
    // Buscar dados da proforma
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT p.*, c.nome as cliente_nome, c.email as cliente_email
              FROM proformas p
              JOIN clientes c ON p.cliente_id = c.id
              WHERE p.id = ? AND p.cliente_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $proforma_id);
    $stmt->bindParam(2, $_SESSION['cliente_id']);
    $stmt->execute();
    $proforma = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($proforma) {
        // Buscar itens da proforma
        $query_itens = "SELECT pi.*, prod.nome, prod.preco, prod.descricao
                       FROM proforma_itens pi
                       JOIN produtos prod ON pi.produto_id = prod.id
                       WHERE pi.proforma_id = ?";
        $stmt_itens = $db->prepare($query_itens);
        $stmt_itens->bindParam(1, $proforma_id);
        $stmt_itens->execute();
        $itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
        
        // Gerar HTML para impressão/PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Proforma #' . $proforma_id . ' - ' . EMPRESA_NOME . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    color: #333;
                }
                .container { 
                    max-width: 800px; 
                    margin: 0 auto; 
                    border: 2px solid #3498db; 
                    padding: 30px; 
                    position: relative;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px;
                    border-bottom: 2px solid #3498db;
                    padding-bottom: 20px;
                }
                .company-info {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .client-info, .proforma-info {
                    margin-bottom: 20px;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin: 20px 0;
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 12px; 
                    text-align: left;
                }
                th { 
                    background-color: #3498db; 
                    color: white;
                }
                .total-row { 
                    background-color: #f8f9fa; 
                    font-weight: bold;
                }
                .footer { 
                    margin-top: 40px; 
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #666;
                }
                .watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 60px;
                    color: rgba(52, 152, 219, 0.1);
                    pointer-events: none;
                    z-index: -1;
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="watermark">' . EMPRESA_NOME . '</div>
                
                <div class="header">
                    <h1>PROFORMA</h1>
                    <h2>#' . $proforma_id . '</h2>
                </div>
                
                <div class="company-info">
                    <h3>' . EMPRESA_NOME . '</h3>
                    <p>' . EMPRESA_ENDERECO . ' | ' . EMPRESA_TELEFONE . ' | ' . EMPRESA_EMAIL . '</p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div class="client-info">
                        <h4>Cliente</h4>
                        <p><strong>Nome:</strong> ' . htmlspecialchars($proforma['cliente_nome']) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($proforma['cliente_email']) . '</p>
                    </div>
                    
                    <div class="proforma-info">
                        <h4>Proforma</h4>
                        <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($proforma['data_criacao'])) . '</p>
                        <p><strong>Status:</strong> ' . strtoupper($proforma['status']) . '</p>
                        <p><strong>Válida até:</strong> ' . date('d/m/Y', strtotime('+7 days', strtotime($proforma['data_criacao']))) . '</p>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th width="100">Preço Unit.</th>
                            <th width="80">Qtd</th>
                            <th width="120">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $total_geral = 0;
        foreach($itens as $item) {
            $subtotal = $item['quantidade'] * $item['preco'];
            $total_geral += $subtotal;
            
            $html .= '
                        <tr>
                            <td>
                                <strong>' . htmlspecialchars($item['nome']) . '</strong><br>
                                <small>' . htmlspecialchars(substr($item['descricao'], 0, 80)) . '...</small>
                            </td>
                            <td>' . number_format($item['preco'], 2, ',', '.') . ' MT</td>
                            <td>' . number_format($item['quantidade'], 0, ',', '.') . '</td>
                            <td>' . number_format($subtotal, 2, ',', '.') . ' MT</td>
                        </tr>';
        }
        
        $html .= '
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>TOTAL GERAL:</strong></td>
                            <td><strong>' . number_format($total_geral, 2, ',', '.') . ' MT</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="footer">
                    <p><strong>Instruções:</strong></p>
                    <ol>
                        <li>Esta proforma é válida por 7 dias úteis a partir da data de emissão</li>
                        <li>Apresente este documento na fábrica para confirmar a compra</li>
                        <li>Para dúvidas, entre em contato: ' . EMPRESA_TELEFONE . '</li>
                        <li>Endereço: ' . EMPRESA_ENDERECO . '</li>
                    </ol>
                    <p style="text-align: center; margin-top: 20px;">
                        <em>Documento gerado automaticamente em ' . date('d/m/Y H:i') . '</em>
                    </p>
                </div>
                
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px;">
                     Imprimir / Salvar como PDF
                </button>
               
                <button onclick="window.close()" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px;">
                     Fechar Janela
                </button>
            </div>
            </div>
            
            <script>
                window.onload = function() {
                    // Auto-print se solicitado
                    if (window.location.search.includes("autoprint")) {
                        window.print();
                    }
                };
            </script>
        </body>
        </html>';
        
        // Enviar cabeçalhos e conteúdo
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="proforma_' . $proforma_id . '.html"');
        echo $html;
        exit;
    }
}

header('Location: cliente_area.php');
exit;
?>