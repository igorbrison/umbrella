CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa ENUM('F','J') NOT NULL,
    cpf_cnpj VARCHAR(18) NOT NULL UNIQUE,
    nome_razao VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);