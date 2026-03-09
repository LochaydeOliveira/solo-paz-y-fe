-- Tabela de Configurações Gerais
-- Criado para o projeto Brasil Hilário

CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('string', 'integer', 'boolean', 'float', 'array', 'json') DEFAULT 'string',
    grupo VARCHAR(50) DEFAULT 'geral',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_grupo (grupo),
    INDEX idx_chave (chave)
);

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, tipo, grupo) VALUES
-- Configurações Gerais
('site_title', 'Brasil Hilário', 'string', 'geral'),
('site_description', 'O melhor do humor brasileiro', 'string', 'geral'),
('site_url', 'https://brasilhilario.com.br', 'string', 'geral'),
('admin_email', 'admin@brasilhilario.com.br', 'string', 'geral'),
('posts_per_page', '10', 'integer', 'geral'),
('primary_color', '#0b8103', 'string', 'geral'),
('secondary_color', '#b30606', 'string', 'geral'),
('logo_url', 'assets/images/logo-brasil-hilario-quadrada-svg.svg', 'string', 'geral'),
('favicon_url', 'assets/images/favicon.ico', 'string', 'geral'),
('comments_active', '1', 'boolean', 'geral'),

-- Configurações SEO
('meta_keywords', 'humor, brasileiro, piadas, memes, comédia', 'string', 'seo'),
('og_image_default', 'assets/images/og-image-default.jpg', 'string', 'seo'),
('google_analytics_id', '', 'string', 'seo'),

-- Redes Sociais
('facebook_url', '', 'string', 'redes_sociais'),
('instagram_url', '', 'string', 'redes_sociais'),
('twitter_url', '', 'string', 'redes_sociais'),
('youtube_url', '', 'string', 'redes_sociais'),
('tiktok_url', '', 'string', 'redes_sociais'),
('telegram_url', '', 'string', 'redes_sociais'),

-- Integração
('head_code', '', 'string', 'integracao'),
('body_code', '', 'string', 'integracao'),
('adsense_code', '', 'string', 'integracao'),

-- Páginas
('newsletter_active', '1', 'boolean', 'paginas'),
('newsletter_title', 'Inscreva-se na Newsletter', 'string', 'paginas'),
('newsletter_description', 'Receba as melhores piadas e memes diretamente no seu email!', 'string', 'paginas'),
('about_page_title', 'Sobre Nós', 'string', 'paginas'),
('contact_page_title', 'Entre em Contato', 'string', 'paginas')

ON DUPLICATE KEY UPDATE
    valor = VALUES(valor),
    tipo = VALUES(tipo),
    grupo = VALUES(grupo),
    atualizado_em = NOW(); 