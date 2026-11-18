CREATE DATABASE IF NOT EXISTS db_kultive CHARACTER SET utf8mb4;
USE db_kultive;
CREATE TABLE IF NOT EXISTS tipagem
(
    tipo_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    definicao VARCHAR(200) NOT NULL,
    categoria VARCHAR(20) NOT NULL DEFAULT 'geral'
);
CREATE TABLE IF NOT EXISTS propriedade 
(
    prop_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    situacao_atual VARCHAR(30) NOT NULL,
    tamanho DECIMAL(10,2) NOT NULL
);
CREATE TABLE IF NOT EXISTS usuario 
(
    usuario_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100),
    usuario VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    tipo_id INT,
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS usuario_propriedade
(
    usuario_id INT NOT NULL,
    prop_id INT NOT NULL,
    PRIMARY KEY (usuario_id, prop_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id)
);
CREATE TABLE IF NOT EXISTS animais 
(
    animal_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    tipo_id INT NOT NULL,
    data_compra DATE,
    data_nasci DATETIME,
    situacao_atual VARCHAR(30),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS maquinas_equipamentos 
(
    maquina_equip_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    data_adquirido DATETIME,
    nome VARCHAR(100) NOT NULL,
    situacao_atual VARCHAR(30) NOT NULL,
    tipo_id INT,
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS area_cultivo 
(
    area_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    tipo_id INT NOT NULL,
    tamanho DECIMAL(10,2) NOT NULL,
    situacao_atual VARCHAR(30),
    nome VARCHAR(100) NOT NULL,
    conteudo VARCHAR(100),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS prontuario 
(
    prontuario_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    usuario_id INT NOT NULL,
    animal_id INT,
    maquina_equip_id INT,
    area_id INT,
    descricao VARCHAR(500),
    data_ocorrencia DATETIME,
    nome VARCHAR(100) NOT NULL,
    caminho VARCHAR(255),
    situacao_atual VARCHAR(30),
    tipo_id INT,
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id),
    FOREIGN KEY (animal_id) REFERENCES animais(animal_id),
    FOREIGN KEY (maquina_equip_id) REFERENCES maquinas_equipamentos(maquina_equip_id),
    FOREIGN KEY (area_id) REFERENCES area_cultivo(area_id)
);

CREATE TABLE IF NOT EXISTS arquivos 
(
    arquivo_id INT PRIMARY KEY AUTO_INCREMENT,
    prontuario_id INT NOT NULL,
    tipo_id INT,
    nome VARCHAR(100) NOT NULL,
    arquivo MEDIUMBLOB NOT NULL,
    data_upload DATE NOT NULL,
    FOREIGN KEY (prontuario_id) REFERENCES prontuario(prontuario_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
