-- Planos disponíveis para contratação
CREATE TABLE planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    descricao TEXT,
    valor DECIMAL(10,2),
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Módulos (funcionalidades do sistema de automação)
-- ATUALIZADO: adicionada a coluna 'valor' para precificação individual de cada módulo.
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(80) NOT NULL,
    valor DECIMAL(10,2) DEFAULT 0.00,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1
);

-- Relacionamento: quais módulos cada plano oferece
CREATE TABLE planos_modulos (
    plano_id INT NOT NULL,
    modulo_id INT NOT NULL,
    PRIMARY KEY (plano_id, modulo_id),
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE CASCADE,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Assinaturas dos representantes a um plano
CREATE TABLE assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    representante_id INT NOT NULL,
    plano_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE DEFAULT NULL,
    status ENUM('ativa','cancelada','expirada') DEFAULT 'ativa',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (representante_id) REFERENCES representantes(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE CASCADE
);