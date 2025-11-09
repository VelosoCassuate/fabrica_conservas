
-- Inserir 20 produtos na base de dados
INSERT INTO produtos (nome, preco, exportacao, material_embalagem, preco_embalagem, descricao, imagem) VALUES
('Atum em Azeite Extra', 285.00, TRUE, 'Lata Premium', 25.00, 'Atum de alto mar conservado em azeite extra virgem, qualidade exportação', NULL),
('Sardinha em Azeite', 125.00, TRUE, 'Lata', 12.00, 'Sardinhas selecionadas conservadas em azeite virgem', NULL),
('Tomate Pelado Integral', 95.00, FALSE, 'Lata', 8.00, 'Tomates pelados inteiros em suco natural, ideal para molhos', NULL),
('Piri-Piri Picante', 65.00, TRUE, 'Vidro', 15.00, 'Pimentas piri-piri extra picantes conservadas em azeite', NULL),
('Feijão Verde em Salmoura', 88.00, TRUE, 'Vidro', 18.00, 'Feijão verde fresco conservado em salmoura leve', NULL),
('Pepinos em Conserva Doce', 92.00, FALSE, 'Vidro', 16.00, 'Pepinos em conserva doce e azeda com especiarias', NULL),
('Cebolinhas em Vinagre', 78.00, TRUE, 'Vidro', 14.00, 'Cebolinhas pequenas conservadas em vinagre branco', NULL),
('Milho Doce em Grão', 75.00, FALSE, 'Lata', 7.00, 'Grãos de milho doce selecionados em água e sal', NULL),
('Ervilhas Tenras', 82.00, TRUE, 'Lata', 9.00, 'Ervilhas verdes tenras conservadas em água salgada', NULL),
('Cogumelos Inteiros', 135.00, TRUE, 'Vidro', 22.00, 'Cogumelos frescos inteiros conservados em salmoura', NULL),
('Pimentos Assados', 145.00, TRUE, 'Vidro', 20.00, 'Pimentos vermelhos assados conservados em azeite', NULL),
('Azeitonas Verdes', 110.00, FALSE, 'Vidro', 18.00, 'Azeitonas verdes com caroço em salmoura', NULL),
('Azeitonas Pretas', 120.00, TRUE, 'Vidro', 19.00, 'Azeitonas pretas sem caroço em azeite e ervas', NULL),
('Cenoura em Conserva', 68.00, FALSE, 'Vidro', 12.00, 'Cenouras baby em conserva agridoce', NULL),
('Beringela em Azeite', 155.00, TRUE, 'Vidro', 24.00, 'Beringela assada conservada em azeite com alho', NULL),
('Feijão Preto Cozido', 72.00, FALSE, 'Lata', 6.00, 'Feijão preto cozido pronto para consumo', NULL),
('Grão-de-Bico Cozido', 70.00, TRUE, 'Lata', 7.00, 'Grão-de-bico cozido em água e sal', NULL),
('Mix de Legumes', 105.00, TRUE, 'Vidro', 20.00, 'Mistura de legumes em conserva para saladas', NULL),
('Pasta de Piri-Piri', 85.00, TRUE, 'Vidro', 16.00, 'Pasta concentrada de piri-piri para tempero', NULL),
('Molho de Tomate Tradicional', 65.00, FALSE, 'Lata', 5.00, 'Molho de tomate natural para massas e pratos', NULL);


-- Caso dê errado tente alterar os id's dos produtos
INSERT INTO fabrica_conservas.plano_producao (produto_id, mes, quantidade_planeada)
VALUES (51, 6, 500), (50, 11, 450), (52, 40, 420);