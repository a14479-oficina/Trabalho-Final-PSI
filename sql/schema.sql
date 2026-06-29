-- =========================================================
-- RESET DAS TABELAS (PostgreSQL)
-- =========================================================

DROP TABLE IF EXISTS transacoes CASCADE;
DROP TABLE IF EXISTS cartoes CASCADE;
DROP TABLE IF EXISTS contas CASCADE;
DROP TABLE IF EXISTS utilizadores CASCADE;

-- =========================================================
-- TABELA: utilizadores
-- =========================================================

CREATE TABLE utilizadores (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nif VARCHAR(9),
    email VARCHAR(100) NOT NULL UNIQUE,
    palavra_passe VARCHAR(255) NOT NULL,
    tipo_utilizador VARCHAR(10) NOT NULL DEFAULT 'cliente'
        CHECK (tipo_utilizador IN ('admin', 'cliente')),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================================
-- TABELA: contas
-- =========================================================

CREATE TABLE contas (
    id SERIAL PRIMARY KEY,
    utilizador_id INT NOT NULL,
    numero_conta VARCHAR(25) NOT NULL UNIQUE,
    tipo_conta VARCHAR(10) NOT NULL
        CHECK (tipo_conta IN ('corrente', 'poupanca')),
    saldo NUMERIC(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_contas_utilizadores
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizadores(id)
        ON DELETE CASCADE
);

-- =========================================================
-- TABELA: cartoes
-- =========================================================

CREATE TABLE cartoes (
    id SERIAL PRIMARY KEY,
    conta_id INT NOT NULL,
    numero_cartao VARCHAR(16) NOT NULL UNIQUE,
    pin_encriptado VARCHAR(255) NOT NULL,
    estado VARCHAR(10) NOT NULL DEFAULT 'ativo'
        CHECK (estado IN ('ativo', 'bloqueado')),
    validade DATE NOT NULL,

    CONSTRAINT fk_cartoes_contas
        FOREIGN KEY (conta_id)
        REFERENCES contas(id)
        ON DELETE CASCADE
);

-- =========================================================
-- TABELA: transacoes
-- =========================================================

CREATE TABLE transacoes (
    id SERIAL PRIMARY KEY,
    conta_origem_id INT,
    conta_destino_id INT,
    tipo_transacao VARCHAR(20) NOT NULL
        CHECK (tipo_transacao IN ('deposito', 'levantamento', 'transferencia', 'pagamento')),
    valor NUMERIC(10,2) NOT NULL,
    data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_transacoes_origem
        FOREIGN KEY (conta_origem_id)
        REFERENCES contas(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_transacoes_destino
        FOREIGN KEY (conta_destino_id)
        REFERENCES contas(id)
        ON DELETE SET NULL
);

-- =========================================================
-- DADOS DE TESTE
-- =========================================================

INSERT INTO utilizadores (id, nome, nif, email, palavra_passe, tipo_utilizador) VALUES 
(1, 'Admin', NULL, 'admin@devbank.pt', '$2y$10$7R9jQnW88H6Z4bX9vK8MueO1rA9zVbG8xY1h9f8d7c6b5a4m3l2k1', 'admin'),
(2, 'Ana Silva', '254123987', 'ana.silva@escola.pt', '$2y$10$7R9jQnW88H6Z4bX9vK8MueO1rA9zVbG8xY1h9f8d7c6b5a4m3l2k1', 'cliente'),
(3, 'Rui Santos', '210987654', 'rui.santos@escola.pt', '$2y$10$7R9jQnW88H6Z4bX9vK8MueO1rA9zVbG8xY1h9f8d7c6b5a4m3l2k1', 'cliente');

INSERT INTO contas (id, utilizador_id, numero_conta, tipo_conta, saldo) VALUES 
(1, 2, 'PT5000010001234567890', 'corrente', 1250.50),
(2, 2, 'PT5000010001234567891', 'poupanca', 5000.00),
(3, 3, 'PT5000020009876543210', 'corrente', 450.00);

INSERT INTO cartoes (id, conta_id, numero_cartao, pin_encriptado, estado, validade) VALUES 
(1, 1, '5044123456789012', '$2y$10$w4r6b7X4oA8Vn7Lg2K7MueR6T2xVb7mK3Y7O5eG8zW1h9f8d7c6b5', 'ativo', '2030-12-31'),
(2, 3, '5044987654321098', '$2y$10$w4r6b7X4oA8Vn7Lg2K7MueR6T2xVb7mK3Y7O5eG8zW1h9f8d7c6b5', 'ativo', '2029-08-31');

INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES 
(NULL, 1, 'deposito', 1000.00),
(1, 3, 'transferencia', 150.00);
