--  Opções B3 -- Database schema (PostgreSQL)
--  psql -U opcoes_user -d opcoes_b3 -f database/schema.sql

BEGIN;

CREATE TABLE usuarios (
    id         SERIAL PRIMARY KEY,
    nome       VARCHAR(120)  NOT NULL,
    email      VARCHAR(160)  NOT NULL UNIQUE,
    senha_hash VARCHAR(255)  NOT NULL,
    criado_em  TIMESTAMP     NOT NULL DEFAULT NOW()
);

CREATE TABLE contratos (
    id               SERIAL PRIMARY KEY,
    ativo            VARCHAR(20)   NOT NULL,
    tipo_opcao       VARCHAR(4)    NOT NULL CHECK (tipo_opcao IN ('CALL', 'PUT')),
    preco_exercicio  NUMERIC(12,2) NOT NULL CHECK (preco_exercicio > 0),
    data_vencto      DATE          NOT NULL,
    preco_atual      NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (preco_atual >= 0),
    criado_em        TIMESTAMP     NOT NULL DEFAULT NOW()
);

CREATE TABLE posicoes (
    id               SERIAL PRIMARY KEY,
    usuario_id       INT           NOT NULL REFERENCES usuarios(id),
    contrato_id      INT           NOT NULL REFERENCES contratos(id),
    quantidade_total INT           NOT NULL CHECK (quantidade_total > 0),
    quantidade_aberta INT          NOT NULL DEFAULT 0 CHECK (quantidade_aberta >= 0),
    preco_medio      NUMERIC(12,2) NOT NULL CHECK (preco_medio >= 0),
    data_abertura    TIMESTAMP     NOT NULL DEFAULT NOW(),
    status           VARCHAR(10)   NOT NULL DEFAULT 'ABERTA' CHECK (status IN ('ABERTA', 'FECHADA')),
    data_fechamento  TIMESTAMP     NULL,
);

CREATE INDEX idx_posicoes_usuario ON posicoes (usuario_id, status);
CREATE INDEX idx_posicoes_contrato ON posicoes (contrato_id);

CREATE TABLE transacoes (
    id               SERIAL PRIMARY KEY,
    usuario_id       INT           NOT NULL REFERENCES usuarios(id),
    contrato_id      INT           NOT NULL REFERENCES contratos(id),
    operacao         VARCHAR(6)    NOT NULL CHECK (operacao IN ('COMPRA', 'VENDA')),
    quantidade       INT           NOT NULL CHECK (quantidade > 0),
    preco            NUMERIC(12,2) NOT NULL CHECK (preco >= 0),
    comissao         NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (comissao >= 0),
    data_transacao   TIMESTAMP     NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_transacoes_usuario ON transacoes (usuario_id, data_transacao DESC);

COMMIT;
