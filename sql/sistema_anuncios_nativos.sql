-- Sistema de Anúncios Nativos
-- Criado para o projeto Brasil Hilário

-- Tabela de anúncios
CREATE TABLE IF NOT EXISTS anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    imagem VARCHAR(500) NOT NULL,
    link_compra VARCHAR(500) NOT NULL,
    localizacao ENUM('sidebar', 'conteudo') NOT NULL,
    cta_ativo BOOLEAN DEFAULT FALSE,
    cta_texto VARCHAR(100) DEFAULT 'Saiba Mais',
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de relacionamento anúncios-posts
CREATE TABLE IF NOT EXISTS anuncios_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT NOT NULL,
    post_id INT NOT NULL,
    FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_anuncio_post (anuncio_id, post_id)
);

-- Tabela de cliques nos anúncios
CREATE TABLE IF NOT EXISTS cliques_anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT NOT NULL,
    post_id INT NOT NULL,
    tipo_clique ENUM('imagem', 'titulo', 'cta') NOT NULL,
    ip_usuario VARCHAR(45),
    user_agent TEXT,
    data_clique TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Criar índices apenas se não existirem
-- Verificar se o índice existe antes de criar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'anuncios' 
     AND INDEX_NAME = 'idx_anuncios_localizacao') = 0,
    'CREATE INDEX idx_anuncios_localizacao ON anuncios(localizacao, ativo)',
    'SELECT "Índice idx_anuncios_localizacao já existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'anuncios_posts' 
     AND INDEX_NAME = 'idx_anuncios_posts_post_id') = 0,
    'CREATE INDEX idx_anuncios_posts_post_id ON anuncios_posts(post_id)',
    'SELECT "Índice idx_anuncios_posts_post_id já existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'cliques_anuncios' 
     AND INDEX_NAME = 'idx_cliques_anuncios_data') = 0,
    'CREATE INDEX idx_cliques_anuncios_data ON cliques_anuncios(data_clique)',
    'SELECT "Índice idx_cliques_anuncios_data já existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'cliques_anuncios' 
     AND INDEX_NAME = 'idx_cliques_anuncios_anuncio') = 0,
    'CREATE INDEX idx_cliques_anuncios_anuncio ON cliques_anuncios(anuncio_id)',
    'SELECT "Índice idx_cliques_anuncios_anuncio já existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Inserir alguns anúncios de exemplo (sem associações com posts)
INSERT INTO anuncios (titulo, imagem, link_compra, localizacao, cta_ativo, cta_texto) VALUES
('Descubra os melhores preços em Caucaia', '/assets/img/anuncios/loja-caucaia.jpg', 'https://exemplo.com/loja-caucaia', 'sidebar', TRUE, 'Ver Ofertas'),
('Graduação com desconto especial', '/assets/img/anuncios/graduacao-wyden.jpg', 'https://exemplo.com/graduacao', 'sidebar', TRUE, 'Inscreva-se'),
('Pneus com preços imperdíveis', '/assets/img/anuncios/pneus-caucaia.jpg', 'https://exemplo.com/pneus', 'conteudo', FALSE, ''),
('Receita argentina com toque brasileiro', '/assets/img/anuncios/receita-argentina.jpg', 'https://exemplo.com/receita', 'conteudo', TRUE, 'Ver Receita');

-- NOTA: As associações com posts devem ser feitas manualmente através do painel admin
-- após verificar quais posts existem no banco de dados 