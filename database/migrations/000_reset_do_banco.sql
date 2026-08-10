-- Desabilitar verificação de foreign keys temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpar tabelas filhas primeiro
DELETE FROM logs_cobranca;
DELETE FROM tokens_renovacao;
DELETE FROM licencas;
DELETE FROM pagamentos;
DELETE FROM cliente_modulos;
DELETE FROM solicitacoes;
DELETE FROM clientes;

-- Limpar tabelas principais (exceto administradores)
DELETE FROM representantes;
DELETE FROM modulos;
DELETE FROM configuracoes;

-- Reinserir a configuração padrão de salário mínimo
INSERT INTO configuracoes (chave, valor) VALUES ('salario_minimo', 1621.00);

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;