-- Sistema Avançado de Configurações
-- Tabelas para gerenciamento completo do site via admin

-- Tabela de configurações visuais (cores, fontes, etc.)
CREATE TABLE IF NOT EXISTS configuracoes_visuais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(50) NOT NULL, -- 'cores', 'fontes', 'layout'
    elemento VARCHAR(100) NOT NULL, -- 'header', 'footer', 'titulo', 'paragrafo'
    propriedade VARCHAR(50) NOT NULL, -- 'cor', 'fonte', 'tamanho', 'peso'
    valor VARCHAR(255) NOT NULL,
    tipo ENUM('cor', 'fonte', 'numero', 'texto', 'boolean') DEFAULT 'texto',
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_config (categoria, elemento, propriedade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de seções do site
CREATE TABLE IF NOT EXISTS secoes_site (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('header', 'footer', 'sidebar', 'content', 'custom') DEFAULT 'custom',
    posicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    configuracoes JSON,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de elementos das seções (menus, logos, etc.)
CREATE TABLE IF NOT EXISTS elementos_secao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secao_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- 'menu', 'logo', 'texto', 'imagem', 'link'
    nome VARCHAR(100) NOT NULL,
    conteudo TEXT,
    configuracoes JSON,
    posicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (secao_id) REFERENCES secoes_site(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de itens de menu
CREATE TABLE IF NOT EXISTS itens_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elemento_id INT NOT NULL,
    texto VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    tipo ENUM('interno', 'externo', 'categoria', 'pagina') DEFAULT 'interno',
    alvo VARCHAR(20) DEFAULT '_self', -- '_self', '_blank'
    icone VARCHAR(50),
    posicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (elemento_id) REFERENCES elementos_secao(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de integrações
CREATE TABLE IF NOT EXISTS integracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plataforma VARCHAR(50) NOT NULL, -- 'google', 'facebook', 'twitter', 'instagram'
    tipo VARCHAR(50) NOT NULL, -- 'analytics', 'ads', 'social', 'api'
    nome VARCHAR(100) NOT NULL,
    chave_api VARCHAR(255),
    chave_secreta VARCHAR(255),
    configuracoes JSON,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de configurações de comentários
CREATE TABLE IF NOT EXISTS configuracoes_comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('boolean', 'texto', 'numero', 'json') DEFAULT 'texto',
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de configurações de usuários
CREATE TABLE IF NOT EXISTS configuracoes_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('boolean', 'texto', 'numero', 'json') DEFAULT 'texto',
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir dados padrão para configurações visuais
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo) VALUES
-- Cores principais
('cores', 'site', 'cor_primaria', '#007bff', 'cor'),
('cores', 'site', 'cor_secundaria', '#6c757d', 'cor'),
('cores', 'site', 'cor_sucesso', '#28a745', 'cor'),
('cores', 'site', 'cor_perigo', '#dc3545', 'cor'),
('cores', 'site', 'cor_aviso', '#ffc107', 'cor'),
('cores', 'site', 'cor_info', '#17a2b8', 'cor'),

-- Cores do header
('cores', 'header', 'cor_fundo', '#ffffff', 'cor'),
('cores', 'header', 'cor_texto', '#333333', 'cor'),
('cores', 'header', 'cor_link', '#007bff', 'cor'),
('cores', 'header', 'cor_link_hover', '#0056b3', 'cor'),

-- Cores do footer
('cores', 'footer', 'cor_fundo', '#f8f9fa', 'cor'),
('cores', 'footer', 'cor_texto', '#6c757d', 'cor'),
('cores', 'footer', 'cor_link', '#007bff', 'cor'),

-- Fontes principais
('fontes', 'site', 'fonte_principal', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte'),
('fontes', 'site', 'fonte_titulos', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte'),
('fontes', 'site', 'fonte_texto', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte'),

-- Tamanhos de fonte
('fontes', 'titulo', 'tamanho', '28px', 'texto'),
('fontes', 'subtitulo', 'tamanho', '20px', 'texto'),
('fontes', 'paragrafo', 'tamanho', '16px', 'texto'),
('fontes', 'pequeno', 'tamanho', '14px', 'texto');

-- Inserir seções padrão
INSERT INTO secoes_site (nome, slug, tipo, posicao, configuracoes) VALUES
('Header', 'header', 'header', 1, '{"altura": "80px", "fixo": false}'),
('Footer', 'footer', 'footer', 2, '{"altura": "auto", "colunas": 4}'),
('Sidebar', 'sidebar', 'sidebar', 3, '{"largura": "300px", "posicao": "direita"}'),
('Conteúdo Principal', 'content', 'content', 4, '{"largura": "100%", "padding": "20px"}');

-- Inserir elementos padrão
INSERT INTO elementos_secao (secao_id, tipo, nome, conteudo, posicao) VALUES
(1, 'logo', 'Logo Principal', 'Brasil Hilário', 1),
(1, 'menu', 'Menu Principal', '', 2),
(2, 'texto', 'Copyright', '© 2024 Brasil Hilário. Todos os direitos reservados.', 1),
(2, 'menu', 'Menu Footer', '', 2);

-- Inserir itens de menu padrão
INSERT INTO itens_menu (elemento_id, texto, url, tipo, posicao) VALUES
(2, 'Início', '/', 'interno', 1),
(2, 'Sobre', '/sobre', 'interno', 2),
(2, 'Contato', '/contato', 'interno', 3),
(4, 'Política de Privacidade', '/privacidade', 'interno', 1),
(4, 'Termos de Uso', '/termos', 'interno', 2);

-- Inserir configurações de comentários padrão
INSERT INTO configuracoes_comentarios (chave, valor, tipo, descricao) VALUES
('comentarios_ativos', '1', 'boolean', 'Ativar sistema de comentários'),
('moderacao_obrigatoria', '0', 'boolean', 'Comentários precisam de aprovação'),
('limite_comentarios', '10', 'numero', 'Número máximo de comentários por post'),
('notificacao_email', '1', 'boolean', 'Enviar notificação por email para novos comentários');

-- Inserir configurações de usuários padrão
INSERT INTO configuracoes_usuarios (chave, valor, tipo, descricao) VALUES
('registro_publico', '0', 'boolean', 'Permitir registro público de usuários'),
('confirmacao_email', '1', 'boolean', 'Exigir confirmação de email'),
('avatar_padrao', '/assets/img/avatar-default.png', 'texto', 'Avatar padrão para usuários'),
('permissoes_padrao', '{"posts": "read", "comentarios": "write"}', 'json', 'Permissões padrão para novos usuários'); 