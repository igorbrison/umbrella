-- Módulos (com percentual do salário mínimo)
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(80) NOT NULL,
    percentual_salario_minimo DECIMAL(5,2) DEFAULT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1
);