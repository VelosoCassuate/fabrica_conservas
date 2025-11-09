<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'fabrica_conservas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('ADMIN_USERS', [
  'admin' => password_hash('admin123', PASSWORD_DEFAULT),
  'gerente' => password_hash('gerente123', PASSWORD_DEFAULT)
]);

// Configurações do site
define('SITE_NAME', 'BConserves - Fábrica de Conservas');
define('SITE_URL', 'http://localhost/fabrica_conservas');

// Configurações da empresa
define('EMPRESA_NOME', 'BConserves');
define('EMPRESA_ENDERECO', 'Ponta Gea');
define('EMPRESA_TELEFONE', '+258 86 334 5668');
define('EMPRESA_EMAIL', 'bconserves@gmail.com');
define('EMPRESA_HORARIO', 'Segunda a Sexta: 8h00 - 19h00');
define('EMPRESA_GERENTE', 'BConserves company');
?>