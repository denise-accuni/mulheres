-- SQL de criação do banco de dados para o Observatório Mulheres na Computação

-- Criação do banco
CREATE DATABASE IF NOT EXISTS observatorio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE observatorio;

-- Tabela de continentes
CREATE TABLE IF NOT EXISTS continentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- Tabela de países
CREATE TABLE IF NOT EXISTS paises (
    codigo_iso CHAR(3) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    continente_id INT,
    FOREIGN KEY (continente_id) REFERENCES continentes(id)
);

-- Tabela de perfis (mulheres)
CREATE TABLE IF NOT EXISTS perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    biografia TEXT,
    imagem_url VARCHAR(255),
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    continente_id INT,
    FOREIGN KEY (continente_id) REFERENCES continentes(id)
);

-- Relação muitos-para-muitos entre perfis e países (uma mulher pode estar associada a vários países)
CREATE TABLE IF NOT EXISTS perfil_pais (
    perfil_id INT NOT NULL,
    pais_codigo_iso CHAR(3) NOT NULL,
    PRIMARY KEY (perfil_id, pais_codigo_iso),
    FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    FOREIGN KEY (pais_codigo_iso) REFERENCES paises(codigo_iso)
);

-- Tabela de áreas de atuação
CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Relação muitos-para-muitos entre perfis e áreas
CREATE TABLE IF NOT EXISTS perfil_area (
    perfil_id INT NOT NULL,
    area_id INT NOT NULL,
    PRIMARY KEY (perfil_id, area_id),
    FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    FOREIGN KEY (area_id) REFERENCES areas(id)
);

-- Tabela de conquistas associadas a cada perfil
CREATE TABLE IF NOT EXISTS conquistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perfil_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    fonte VARCHAR(255),
    FOREIGN KEY (perfil_id) REFERENCES perfis(id)
);

-- Tabela de usuários (opcional) para curadores, aprovadores e administradores
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    permissao ENUM('curador','aprovador','administrador','convidado') DEFAULT 'convidado'
);

-- Tabela de logs de erros para registrar falhas de APIs ou do sistema
CREATE TABLE IF NOT EXISTS logs_erros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);