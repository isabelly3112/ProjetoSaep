CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS pacientes;
DROP TABLE IF EXISTS acompanhantes;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE acompanhantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf_criptografado VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE pacientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf_criptografado VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    acompanhante_id INT UNSIGNED NULL,
    CONSTRAINT fk_paciente_acompanhante FOREIGN KEY (acompanhante_id)
        REFERENCES acompanhantes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE agendamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT UNSIGNED NOT NULL,
    data_consulta DATE NOT NULL,
    hora_consulta TIME NOT NULL,
    CONSTRAINT fk_agendamento_paciente FOREIGN KEY (paciente_id)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO usuarios (id, nome, usuario, senha_hash) VALUES
(1, 'Gestor Geral', 'admin', '$2y$10$Rew5gK08YJya61IMfad6E.xJjwN8qlD0ICqwAycg.45L0xtwfQG.q'),
(2, 'Atendimento Clínica', 'recepcao', '$2y$10$ZGhijc8S5J9htLvPqyB/Se7vVLDXqUixvpmvtCnaZW06HeUBwnNoi'),
(3, 'Dra. Helena Mendes', 'drsilva', '$2y$10$QUpvcsUdqENNu8aH2iTUluPvN/xZF7a0fxAdJYxlBgqHYaGfafB7e');

INSERT INTO acompanhantes (id, nome, cpf_criptografado, telefone) VALUES
(1, 'Luciana Ferreira', 'N3I0nf45yVcNUwljlwQJGc3xefBPqaNqwJCHuzIEaTaDko0/UA+O', '(16) 99123-4401'),
(2, 'Roberto Vasconcelos', 'kiYrntsKeNamptkTT09fKr+3MiPaD/ZMxhQOkbr23Lp9AB9Qrp/p', '(16) 99123-4402'),
(3, 'Patrícia Nogueira', 'hNjke7srRHBPy775vScCThf1MvXPDFQxTte7AvU9Hp6DWmiTomIR', '(16) 99123-4403');

INSERT INTO pacientes (id, nome, cpf_criptografado, data_nascimento, telefone, acompanhante_id) VALUES
(1, 'Marcos Vinícius Rocha', 'tonHOeEYGqMHNFRVa4Ljet/e13wL3vnGfqagbqwfduxPrmBNpqSu', '1988-03-19', '(16) 98210-5501', 1),
(2, 'Camila Duarte', 'd0VrwTaX3b5WMg1u5y3b1YjYwN5zOKi2gqvOBKTJDqCnTaSdPGvl', '1995-11-14', '(16) 98210-5502', 1),
(3, 'Ricardo Almeida', 'SRIF5w5rmAnrm+YyLw21wfjUB+yCrIypggdVJIttV3MIvpKYaeq9', '1974-07-08', '(16) 98210-5503', NULL),
(4, 'Juliana Teixeira', 'N3I0nf45yVcNUwljlwQJGc3xefBPqaNqwJCHuzIEaTaDko0/UA+O', '2003-02-27', '(16) 98210-5504', 2);

INSERT INTO agendamentos (id, paciente_id, data_consulta, hora_consulta) VALUES
(1, 1, '2026-12-03', '08:30:00'),
(2, 3, '2026-12-01', '15:00:00'),
(3, 1, '2026-12-10', '11:00:00'),
(4, 2, '2026-12-01', '09:45:00');
