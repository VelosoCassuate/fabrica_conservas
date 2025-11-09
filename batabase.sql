-- Criar banco de dados
CREATE DATABASE fabrica_conservas;
USE fabrica_conservas;

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    exportacao BOOLEAN DEFAULT FALSE,
    material_embalagem VARCHAR(255),
    preco_embalagem DECIMAL(10,2) DEFAULT 0,
    descricao TEXT,
    imagem VARCHAR(255)
);

-- Tabela de plano de produção
CREATE TABLE plano_producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT,
    mes INT,
    quantidade_planeada INT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de produção real
CREATE TABLE producao_real (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT,
    mes INT,
    dia INT,
    quantidade INT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    chave_acesso VARCHAR(255) NOT NULL
);

-- Tabela de proformas
CREATE TABLE proformas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'confirmada') DEFAULT 'pendente',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabela de itens da proforma
CREATE TABLE proforma_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proforma_id INT,
    produto_id INT,
    quantidade INT,
    FOREIGN KEY (proforma_id) REFERENCES proformas(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de reclamações/sugestões
CREATE TABLE reclamacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    tipo ENUM('reclamacao', 'sugestao') NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserir alguns produtos de exemplo
INSERT INTO produtos (nome, preco, exportacao, material_embalagem, preco_embalagem, descricao) VALUES
('Atum em Azeite', 3.50, TRUE, 'Lata', 0.50, 'Atum de alta qualidade conservado em azeite extra virgem'),
('Sardinha em Molho de Tomate', 2.80, FALSE, NULL, NULL, 'Sardinhas selecionadas em molho de tomate natural'),
('Feijão Verde', 1.90, TRUE, 'Vidro', 0.80, 'Feijão verde fresco conservado em salmoura'),
('Pêssego em Calda', 4.20, TRUE, 'Lata', 0.50, 'Pêssegos maduros em calda açucarada'),
('Tomate Pelado', 2.10, FALSE, NULL, NULL, 'Tomates pelados inteiros em suco natural');

-- Inserir plano de produção de exemplo
INSERT INTO plano_producao (produto_id, mes, quantidade_planeada) VALUES
(1, 1, 10000), (1, 2, 12000), (1, 3, 11000),
(2, 1, 8000), (2, 2, 9000), (2, 3, 8500),
(3, 1, 6000), (3, 2, 7000), (3, 3, 6500),
(4, 1, 5000), (4, 2, 5500), (4, 3, 5200),
(5, 1, 7000), (5, 2, 7500), (5, 3, 7200);

-- Inserir produção real de exemplo
INSERT INTO producao_real (produto_id, mes, dia, quantidade) VALUES
(1, 1, 5, 350), (1, 1, 10, 420), (1, 1, 15, 380),
(2, 1, 5, 280), (2, 1, 10, 310), (2, 1, 15, 290);

-- Inserir cliente de exemplo
INSERT INTO clientes (nome, email, chave_acesso) VALUES
('Supermercado Central', 'central@email.com', 'chave123');


-- Tabela para logs de acesso (adicionar ao script SQL existente)
CREATE TABLE log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('cliente', 'admin'),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Adicionar coluna 'ativo' à tabela clientes
ALTER TABLE clientes ADD COLUMN ativo BOOLEAN DEFAULT TRUE;

-- Atualizar cliente exemplo para ter a coluna ativo
UPDATE clientes SET ativo = TRUE WHERE id = 1;






SELECT * FROM clientes;



-- -- Adicionar coluna de imagem aos produtos
-- ALTER TABLE produtos ADD COLUMN imagem VARCHAR(255) NULL;

-- Criar tabela de logs de acesso se não existir
CREATE TABLE IF NOT EXISTS log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('cliente', 'admin'),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Verificar a estrutura da tabela proformas
DESCRIBE proformas;

-- Se necessário, atualizar a coluna status
ALTER TABLE proformas 
MODIFY COLUMN status ENUM('pendente', 'confirmada', 'cancelada') DEFAULT 'pendente';

-- -- Verificar os dados atuais
-- SELECT id, status FROM proformas;
