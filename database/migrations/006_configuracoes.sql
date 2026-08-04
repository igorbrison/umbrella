CREATE TABLE configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor DECIMAL(10,2) NOT NULL
);

INSERT INTO configuracoes (chave, valor) VALUES ('salario_minimo', 1621.00);