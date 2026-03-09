-- Script para implementar o novo sistema de configuração de fontes
-- Adiciona configurações para fonte geral vs personalizada

-- Configuração de fonte geral do site
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo, ativo) VALUES
('fontes', 'site', 'fonte_geral', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'site', 'usar_fonte_geral', '1', 'boolean', 1),
('fontes', 'site', 'personalizar_fontes', '0', 'boolean', 1);

-- Configurações individuais de fontes (quando personalizar_fontes = 1)
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo, ativo) VALUES
('fontes', 'titulos', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'paragrafos', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'navegacao', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'sidebar', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'cards', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'botoes', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'meta_textos', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1);

-- Configurações de peso das fontes (independente do modo)
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo, ativo) VALUES
('fontes', 'site', 'peso_titulos', '700', 'texto', 1),
('fontes', 'site', 'peso_paragrafos', '400', 'texto', 1),
('fontes', 'site', 'peso_navegacao', '500', 'texto', 1),
('fontes', 'site', 'peso_sidebar', '400', 'texto', 1),
('fontes', 'site', 'peso_cards', '400', 'texto', 1),
('fontes', 'site', 'peso_botoes', '500', 'texto', 1),
('fontes', 'site', 'peso_meta', '400', 'texto', 1);

-- Configurações de tamanho responsivo (desktop e mobile)
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo, ativo) VALUES
('fontes', 'titulos', 'tamanho_desktop', '28px', 'texto', 1),
('fontes', 'titulos', 'tamanho_mobile', '24px', 'texto', 1),
('fontes', 'subtitulos', 'tamanho_desktop', '20px', 'texto', 1),
('fontes', 'subtitulos', 'tamanho_mobile', '18px', 'texto', 1),
('fontes', 'paragrafos', 'tamanho_desktop', '16px', 'texto', 1),
('fontes', 'paragrafos', 'tamanho_mobile', '14px', 'texto', 1),
('fontes', 'navegacao', 'tamanho_desktop', '14px', 'texto', 1),
('fontes', 'navegacao', 'tamanho_mobile', '12px', 'texto', 1),
('fontes', 'sidebar', 'tamanho_desktop', '14px', 'texto', 1),
('fontes', 'sidebar', 'tamanho_mobile', '12px', 'texto', 1),
('fontes', 'cards', 'tamanho_desktop', '14px', 'texto', 1),
('fontes', 'cards', 'tamanho_mobile', '12px', 'texto', 1),
('fontes', 'botoes', 'tamanho_desktop', '14px', 'texto', 1),
('fontes', 'botoes', 'tamanho_mobile', '12px', 'texto', 1),
('fontes', 'meta_textos', 'tamanho_desktop', '12px', 'texto', 1),
('fontes', 'meta_textos', 'tamanho_mobile', '10px', 'texto', 1);

-- Configurações para seções específicas do blog
INSERT INTO configuracoes_visuais (categoria, elemento, propriedade, valor, tipo, ativo) VALUES
-- Seção "Leia Também"
('fontes', 'leia_tambem', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'leia_tambem', 'peso_titulo', '600', 'texto', 1),
('fontes', 'leia_tambem', 'tamanho_titulo_desktop', '22px', 'texto', 1),
('fontes', 'leia_tambem', 'tamanho_titulo_mobile', '20px', 'texto', 1),
('fontes', 'leia_tambem', 'peso_texto', '400', 'texto', 1),
('fontes', 'leia_tambem', 'tamanho_texto_desktop', '14px', 'texto', 1),
('fontes', 'leia_tambem', 'tamanho_texto_mobile', '12px', 'texto', 1),

-- Seção "Últimas do Portal"
('fontes', 'ultimas_portal', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1),
('fontes', 'ultimas_portal', 'peso_titulo', '600', 'texto', 1),
('fontes', 'ultimas_portal', 'tamanho_titulo_desktop', '22px', 'texto', 1),
('fontes', 'ultimas_portal', 'tamanho_titulo_mobile', '20px', 'texto', 1),
('fontes', 'ultimas_portal', 'peso_texto', '400', 'texto', 1),
('fontes', 'ultimas_portal', 'tamanho_texto_desktop', '14px', 'texto', 1),
('fontes', 'ultimas_portal', 'tamanho_texto_mobile', '12px', 'texto', 1); 