-- --------------------------------------------------------
-- Arquivo: database/representantes.sql
-- Função: ESTRUTURA DA TABELA 'representantes'
-- 
-- Esta tabela armazena os dados dos representantes (clientes/fornecedores)
-- que são empresas (pessoa jurídica). O sistema atualmente foca em CNPJ,
-- mas a estrutura pode ser adaptada para PF no futuro.
-- 
-- Compatível com MySQL/MariaDB.
-- --------------------------------------------------------

CREATE TABLE representantes (
    -- Chave primária auto incrementada
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do representante',

    -- Dados cadastrais da empresa
    cnpj VARCHAR(18) NOT NULL UNIQUE COMMENT 'CNPJ com formatação (apenas números no backend, mas pode armazenar com máscara)',
    inscricao_estadual VARCHAR(20) COMMENT 'Inscrição estadual (pode conter letras)',
    nome_razao VARCHAR(150) NOT NULL COMMENT 'Razão social (obrigatório)',
    nome_fantasia VARCHAR(150) COMMENT 'Nome fantasia (opcional, mas recomendado)',
    cnae VARCHAR(20) COMMENT 'Código CNAE principal (atividade econômica)',
    crt VARCHAR(10) COMMENT 'Código de Regime Tributário: 1=Simples Nacional, 2=Simples Nacional excedente, 3=Regime Normal, 4=MEI',
    data_fundacao DATE COMMENT 'Data de abertura/fundação da empresa',

    -- Comissão
    comissao_percentual DECIMAL(5,2) COMMENT 'Percentual de comissão (ex: 5.00 = 5%)',

    -- Endereço
    logradouro VARCHAR(100) COMMENT 'Nome da rua/avenida',
    numero VARCHAR(10) COMMENT 'Número do imóvel (pode conter complemento tipo "S/N")',
    complemento VARCHAR(50) COMMENT 'Complemento do endereço (apto, bloco, etc.)',
    bairro VARCHAR(50) COMMENT 'Bairro',
    cep VARCHAR(10) COMMENT 'CEP com ou sem máscara (apenas números no backend)',
    estado CHAR(2) COMMENT 'UF (sigla de dois caracteres)',
    municipio VARCHAR(80) COMMENT 'Cidade/município',

    -- Contato
    telefone VARCHAR(20) COMMENT 'Telefone fixo',
    celular VARCHAR(20) COMMENT 'Celular (recomendado para WhatsApp)',
    email VARCHAR(100) COMMENT 'E-mail principal (pode ser usado para login)',
    observacoes TEXT COMMENT 'Observações gerais sobre o representante',

    -- Status e senha
    ativo TINYINT(1) DEFAULT 1 COMMENT '0=Inativo, 1=Ativo (permite desativar sem excluir)',
    senha VARCHAR(255) COMMENT 'Hash da senha (armazenado com password_hash) - pode ser vazio se não usar login',

    -- Auditoria (controle de criação/atualização)
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da criação do registro',
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',

    -- Índices adicionais para melhorar performance das consultas comuns
    INDEX idx_nome_razao (nome_razao),
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de representantes (empresas)';