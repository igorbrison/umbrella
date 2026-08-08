CREATE TABLE solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    representante_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    resposta TEXT DEFAULT NULL,
    status ENUM('pendente','deferido','indeferido','em_desenvolvimento','teste','concluido') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (representante_id) REFERENCES representantes(id) ON DELETE CASCADE
);