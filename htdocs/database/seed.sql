--  Opções B3 -- Seed
--  psql -U opcoes_user -d opcoes_b3 -f database/seed.sql
--   admin@exemplo.com        -> admin123
--   usuario@exemplo.com      -> opcoes2026

BEGIN;

INSERT INTO usuarios (nome, email, senha_hash) VALUES
('Administrador',  'admin@exemplo.com',      '$2y$12$9fm2hsbUTd1jM1kMug9dEuKIbOY91qteXZSbr4XWBlbMFNMIOm2cW'),
('Operador_demo',  'usuario@exemplo.com',    '$2y$12$fdNp3buQ/A4bfQy8o00VyuVN.EEdODgYROuc.AdUtPcm/CNpPEtXy');

INSERT INTO contratos (ativo, tipo_opcao, preco_exercicio, data_vencto, preco_atual) VALUES
('PETR4',         'CALL', 38.00, '2026-10-17', 1.8500),
('PETR4',         'PUT',  35.00, '2026-10-17', 0.9500),
('IVVB11',        'CALL', 125.00,'2026-11-20', 2.3500),
('ITUB4',         'CALL', 40.00, '2026-10-17', 1.1000),
('PETR4',         'CALL', 42.00, '2026-12-18', 0.7500);

-- Posições do administrador
-- Contrato comprado PETR4 CALL R$38: 200 contratos a R$1.5000 (aberta)
INSERT INTO posicoes (usuario_id, contrato_id, quantidade_total, quantidade_aberta, preco_medio) VALUES
(1, 1, 200, 200, 1.5000);

-- IVVB11 CALL R$125: total 100, aberta 50 (parcialmente fechada antes via venda)
INSERT INTO posicoes (usuario_id, contrato_id, quantidade_total, quantidade_aberta, preco_medio) VALUES
(1, 3, 100, 50, 2.1000);

-- PETR4 PUT R$35: total 100, fechada
INSERT INTO posicoes (usuario_id, contrato_id, quantidade_total, quantidade_aberta, preco_medio, status, data_fechamento) VALUES
(1, 2, 100, 0, 0.8000, 'FECHADA', NOW() - INTERVAL '7 days');

-- Posições do usuario demo
-- ITUB4 CALL R$40: aberta 50 a 1.0500
INSERT INTO posicoes (usuario_id, contrato_id, quantidade_total, quantidade_aberta, preco_medio) VALUES
(2, 4, 50, 50, 1.0500);

-- Histórico de transações do administrador
-- Compra PETR4 CALL R$38 -- 200 a 1.5000 + comm 8.00
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(1, 1, 'COMPRA', 200, 1.5000, 8.00, NOW() - INTERVAL '5 days');

-- Compra IVVB11 CALL R$125 -- 100 a 2.1000 + comm 5.00
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(1, 3, 'COMPRA', 100, 2.1000, 5.00, NOW() - INTERVAL '10 days');

-- Compra PETR4 PUT R$35 -- 100 a 0.8000 (aberta e depois fechada)
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(1, 2, 'COMPRA', 100, 0.8000, 5.00, NOW() - INTERVAL '30 days');

-- Venda PETR4 PUT R$35 -- fecha posição de 100 a 1.2000
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(1, 2, 'VENDA', 100, 1.2000, 6.00, NOW() - INTERVAL '7 days');

-- Parcial venda IVVB11 CALL R$125 -- 50 a 2.4000
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(1, 3, 'VENDA', 50, 2.4000, 4.00, NOW() - INTERVAL '2 days');

-- Transação - Compra ITUB4 CALL demo user (50 a 1.0500 + comm 3.00)
INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES
(2, 4, 'COMPRA', 50, 1.0500, 3.00, NOW() - INTERVAL '3 days');

COMMIT;
