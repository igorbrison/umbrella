-- Módulos contratados por cliente
CREATE TABLE cliente_modulos (
    cliente_id INT NOT NULL,
    modulo_id INT NOT NULL,
    PRIMARY KEY (cliente_id, modulo_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Licenças dos clientes
CREATE TABLE licencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL UNIQUE,
    chave VARCHAR(64) NOT NULL,
    data_expiracao DATE NOT NULL,
    ativa TINYINT(1) DEFAULT 1,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Tokens para renovação offline manual
CREATE TABLE tokens_renovacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    token VARCHAR(32) NOT NULL,
    chave_liberacao VARCHAR(64) DEFAULT NULL,
    usado TINYINT(1) DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);