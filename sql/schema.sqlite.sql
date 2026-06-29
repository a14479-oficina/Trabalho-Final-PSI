-- =========================================================
-- RESET DAS TABELAS (SQLite)
-- =========================================================

DROP TABLE IF EXISTS transacoes;
DROP TABLE IF EXISTS cartoes;
DROP TABLE IF EXISTS contas;
DROP TABLE IF EXISTS utilizadores;

PRAGMA foreign_keys = ON;

-- =========================================================
-- TABELA: utilizadores
-- =========================================================

CREATE TABLE utilizadores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    nif VARCHAR(9),
    email VARCHAR(100) NOT NULL UNIQUE,
    palavra_passe VARCHAR(255) NOT NULL,
    tipo_utilizador VARCHAR(10) NOT NULL DEFAULT 'cliente'
        CHECK (tipo_utilizador IN ('admin', 'cliente')),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================================================
-- TABELA: contas
-- =========================================================

CREATE TABLE contas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INT NOT NULL,
    numero_conta VARCHAR(25) NOT NULL UNIQUE,
    tipo_conta VARCHAR(10) NOT NULL
        CHECK (tipo_conta IN ('corrente', 'poupanca')),
    saldo REAL NOT NULL DEFAULT 0.00,

    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- =========================================================
-- TABELA: cartoes
-- =========================================================

CREATE TABLE cartoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    conta_id INT NOT NULL,
    numero_cartao VARCHAR(16) NOT NULL UNIQUE,
    pin_encriptado VARCHAR(255) NOT NULL,
    estado VARCHAR(10) NOT NULL DEFAULT 'ativo'
        CHECK (estado IN ('ativo', 'bloqueado')),
    validade DATE NOT NULL,

    FOREIGN KEY (conta_id) REFERENCES contas(id) ON DELETE CASCADE
);

-- =========================================================
-- TABELA: transacoes
-- =========================================================

CREATE TABLE transacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    conta_origem_id INT,
    conta_destino_id INT,
    tipo_transacao VARCHAR(20) NOT NULL
        CHECK (tipo_transacao IN ('deposito', 'levantamento', 'transferencia', 'pagamento')),
    valor REAL NOT NULL,
    data_movimento DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_origem_id) REFERENCES contas(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_destino_id) REFERENCES contas(id) ON DELETE SET NULL
);
