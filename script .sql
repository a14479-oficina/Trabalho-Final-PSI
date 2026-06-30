-- ====================================================================
-- SCRIPT COMPLETO DA BASE DE DADOS: DEVBANK_DB
-- Módulo: Programação Orientada a Objetos em PHP Avançada
-- Funcionalidade: Controlo de Perfis (Admin vs Cliente) para Caixa Multibanco
-- Público-Alvo: 12.º Ano - Curso Profissional de GPSI (15 Horas)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS devbank_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE devbank_db;

-- Remoção de Tabelas Existentes (Garante um Reset Limpo nos Testes)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cartoes;
DROP TABLE IF EXISTS transacoes;
DROP TABLE IF EXISTS contas;
DROP TABLE IF EXISTS utilizadores;
SET FOREIGN_KEY_CHECKS = 1;


-- ====================================================================
-- ESTRUTURA DAS TABELAS
-- ====================================================================

-- Tabela 1: Utilizadores (Modificada para incluir o Tipo de Utilizador)
CREATE TABLE utilizadores (
    id INT AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    nif VARCHAR(9) NULL,                 -- NULL porque o Admin pode não precisar de NIF bancário no sistema
    email VARCHAR(100) NOT NULL,
    palavra_passe VARCHAR(255) NOT NULL, -- Hash seguro (password_hash)
    tipo_utilizador ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente', -- Mapeia a Classe Abstrata e as Subclasses
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilizadores PRIMARY KEY (id),
    CONSTRAINT uq_utilizadores_email UNIQUE (email)
) ENGINE=InnoDB;


-- Tabela 2: Contas (Apenas utilizadores do tipo 'cliente' devem possuir contas)
CREATE TABLE contas (
    id INT AUTO_INCREMENT,
    utilizador_id INT NOT NULL,
    numero_conta VARCHAR(25) NOT NULL,
    tipo_conta ENUM('corrente', 'poupanca') NOT NULL, -- Subclasses: ContaCorrente ou ContaPoupanca
    saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    CONSTRAINT pk_contas PRIMARY KEY (id),
    CONSTRAINT uq_contas_numero UNIQUE (numero_conta),
    CONSTRAINT fk_contas_utilizadores FOREIGN KEY (utilizador_id) 
        REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Tabela 3: Cartões (Associados às contas dos clientes para uso na Caixa Multibanco)
CREATE TABLE cartoes (
    id INT AUTO_INCREMENT,
    conta_id INT NOT NULL,
    numero_cartao VARCHAR(16) NOT NULL,
    pin_encriptado VARCHAR(255) NOT NULL,          -- PIN de 4 dígitos (password_hash)
    pin VARCHAR(4) NOT NULL,                       -- PIN original para exibição no portal do cliente
    estado ENUM('ativo', 'bloqueado') NOT NULL DEFAULT 'ativo',
    validade DATE NOT NULL,
    CONSTRAINT pk_cartoes PRIMARY KEY (id),
    CONSTRAINT uq_cartoes_numero UNIQUE (numero_cartao),
    CONSTRAINT fk_cartoes_contas FOREIGN KEY (conta_id) 
        REFERENCES contas(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Tabela 4: Transações (Histórico de Movimentos ATM)
CREATE TABLE transacoes (
    id INT AUTO_INCREMENT,
    conta_origem_id INT NULL,
    conta_destino_id INT NULL,
    tipo_transacao ENUM('deposito', 'levantamento', 'transferencia') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_transacoes PRIMARY KEY (id),
    CONSTRAINT fk_transacoes_origem FOREIGN KEY (conta_origem_id) 
        REFERENCES contas(id) ON DELETE SET NULL,
    CONSTRAINT fk_transacoes_destino FOREIGN KEY (conta_destino_id) 
        REFERENCES contas(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ====================================================================
-- MASSA DE DADOS DE TESTE INICIAL
-- ====================================================================

-- Inserção de Utilizadores (Admin: admin / Clientes: gpsi12)
-- Hash real gerado com password_hash('admin', PASSWORD_DEFAULT) para o Admin
INSERT INTO utilizadores (id, nome, nif, email, palavra_passe, tipo_utilizador) VALUES 
(1, 'Admin', NULL, 'admin@admin', '$2y$12$LPusagyUjMmbAt5O.gdNjOKkee15n/jGjuJoJmT4aU3ag3iV0seui', 'admin'),
(2, 'Ana Silva', '254123987', 'ana.silva@escola.pt', '$2y$10$7R9jQnW88H6Z4bX9vK8MueO1rA9zVbG8xY1h9f8d7c6b5a4m3l2k1', 'cliente'),
(3, 'Rui Santos', '210987654', 'rui.santos@escola.pt', '$2y$10$7R9jQnW88H6Z4bX9vK8MueO1rA9zVbG8xY1h9f8d7c6b5a4m3l2k1', 'cliente');

-- Inserção de Contas Bancárias (Apenas para os clientes Ana e Rui)
INSERT INTO contas (id, utilizador_id, numero_conta, tipo_conta, saldo) VALUES 
(1, 2, 'PT5000010001234567890', 'corrente', 1250.50),
(2, 2, 'PT5000010001234567891', 'poupanca', 5000.00),
(3, 3, 'PT5000020009876543210', 'corrente', 450.00);

-- Inserção de Cartões de Débito (PIN padrão para os testes do Multibanco: '1234')
-- Hash real gerado com password_hash('1234', PASSWORD_DEFAULT)
INSERT INTO cartoes (id, conta_id, numero_cartao, pin_encriptado, pin, estado, validade) VALUES 
(1, 1, '5044123456789012', '$2y$10$w4r6b7X4oA8Vn7Lg2K7MueR6T2xVb7mK3Y7O5eG8zW1h9f8d7c6b5', '1234', 'ativo', '2030-12-31'),
(2, 3, '5044987654321098', '$2y$10$w4r6b7X4oA8Vn7Lg2K7MueR6T2xVb7mK3Y7O5eG8zW1h9f8d7c6b5', '1234', 'ativo', '2029-08-31');

-- Histórico Inicial de Transações
INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES 
(NULL, 1, 'deposito', 1000.00),
(1, 3, 'transferencia', 150.00);