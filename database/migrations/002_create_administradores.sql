-- Cria a tabela de administradores (acesso ao painel admin)
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insere o administrador padrão (senha: admin123)
-- O hash abaixo corresponde a 'admin123'. Se quiser usar outra senha, gere um novo hash.
INSERT INTO administradores (nome, email, senha) 
VALUES ('Administrador', 'admin@umbrella.com', '$2y$10$XXXXX'); -- substitua pelo hash gerado