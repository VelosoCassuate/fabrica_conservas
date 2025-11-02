<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$mensagem = '';
$tipo_mensagem = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $mensagem_texto = $_POST['mensagem'] ?? '';
    
    if(empty($nome) || empty($email) || empty($tipo) || empty($mensagem_texto)) {
        $mensagem = 'Por favor, preencha todos os campos.';
        $tipo_mensagem = 'erro';
    } else {
        if(salvarReclamacao($nome, $email, $tipo, $mensagem_texto)) {
            $mensagem = 'Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.';
            $tipo_mensagem = 'sucesso';
        } else {
            $mensagem = 'Erro ao enviar mensagem. Tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/site.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo EMPRESA_NOME; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="produtos.php">Produtos</a></li>
                    <li><a href="cliente_login.php">Área do Cliente</a></li>
                    <li><a href="contato.php" class="active">Contato</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="contato">
            <div class="container">
                <h2>Entre em Contato</h2>
                
                <?php if($mensagem): ?>
                    <div class="<?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <div class="contato-info">
                    <h3>Informações de Contato</h3>
                    <p><strong>Endereço:</strong> <?php echo EMPRESA_ENDERECO; ?></p>
                    <p><strong>Telefone:</strong> <?php echo EMPRESA_TELEFONE; ?></p>
                    <p><strong>E-mail:</strong> <?php echo EMPRESA_EMAIL; ?></p>
                    <p><strong>Horário de Atendimento:</strong> <?php echo EMPRESA_HORARIO; ?></p>
                    <p><strong>Gerente:</strong> <?php echo EMPRESA_GERENTE; ?></p>
                </div>
                
                <form method="POST" class="contato-form">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo de Mensagem:</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="reclamacao">Reclamação</option>
                            <option value="sugestao">Sugestão</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem">Mensagem:</label>
                        <textarea id="mensagem" name="mensagem" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Enviar Mensagem</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo EMPRESA_NOME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/site.js"></script>
</body>
</html>