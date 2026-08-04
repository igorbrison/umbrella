CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir admin padrão (senha: admin123) – gere o hash correto com password_hash()
INSERT INTO administradores (nome, email, senha) VALUES ('Administrador', 'admin@umbrella.com', '$2y$10$...');