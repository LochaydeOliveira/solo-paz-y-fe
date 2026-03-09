-- Sistema de Grupos de Anúncios
-- Permite adicionar múltiplos anúncios de uma vez

-- Tabela para grupos de anúncios
CREATE TABLE IF NOT EXISTS grupos_anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    localizacao ENUM('sidebar', 'conteudo') NOT NULL,
    layout ENUM('carrossel', 'grade') DEFAULT 'carrossel',
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela para associar anúncios aos grupos
CREATE TABLE IF NOT EXISTS grupos_anuncios_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    anuncio_id INT NOT NULL,
    ordem INT DEFAULT 0,
    FOREIGN KEY (grupo_id) REFERENCES grupos_anuncios(id) ON DELETE CASCADE,
    FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grupo_anuncio (grupo_id, anuncio_id)
);

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_grupos_localizacao ON grupos_anuncios(localizacao, ativo);
CREATE INDEX IF NOT EXISTS idx_grupos_items_ordem ON grupos_anuncios_items(grupo_id, ordem); 