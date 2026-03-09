-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'editor', 'autor') NOT NULL DEFAULT 'autor',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    avatar VARCHAR(255),
    biografia TEXT,
    ultimo_login TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remover usuários antigos se existirem
DELETE FROM usuarios WHERE email IN ('admin@seusite.com', 'lochaydeguerreiro@hotmail.com');

-- Inserir usuário administrador padrão
INSERT INTO usuarios (
    nome,
    email,
    senha,
    tipo,
    status,
    biografia,
    criado_em
) VALUES (
    'Administrador',
    'lochaydeguerreiro@hotmail.com',
    '$2y$10$FrScbsOokbqdOfcUbFyqneDItRq64xG5jQHknquVBBzP91zFIZkc6',
    'admin',
    'ativo',
    'Administrador do site Brasil Hilário',
    NOW()
); 