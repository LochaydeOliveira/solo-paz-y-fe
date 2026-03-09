<?php
// Configurações do Blog
define('BLOG_TITLE', 'Brasil Hilário');
define('BLOG_DESCRIPTION', 'Conteúdo diário sobre política, futebol, tecnologia, culinária, mundo animal e muito mais.');
define('BLOG_URL', 'https://www.brasilhilario.com.br');
define('BLOG_PATH', ''); // Caminho relativo vazio para a raiz

// Configurações de SEO
define('META_KEYWORDS', 'humor, piadas, memes, vídeos engraçados, notícias engraçadas, brasil hilário');
define('META_DESCRIPTION', 'O melhor conteúdo de humor do Brasil. Piadas, memes, vídeos engraçados e muito mais!');

// Configurações de Cache
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hora

// Configurações de Posts
define('POSTS_PER_PAGE', 10);
define('EXCERPT_LENGTH', 200);

// Configurações de Mídia
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', 'uploads');
define('UPLOAD_URL', BLOG_URL . '/uploads');

// Configurações de Segurança
define('ADMIN_EMAIL', 'admin@brasilhilario.com.br');
define('SECURE_AUTH_KEY', bin2hex(random_bytes(32)));

// Configurações de URLs
define('ADMIN_URL', BLOG_URL . '/admin');
define('API_URL', BLOG_URL . '/api');
define('ASSETS_URL', BLOG_URL . '/assets');

// Configurações de Diretórios
define('ROOT_PATH', '');
define('INCLUDES_PATH', 'includes');
define('ADMIN_PATH', 'admin');
define('API_PATH', 'api');
define('ASSETS_PATH', 'assets');

// Configurações de .htaccess
define('ENABLE_HTACCESS', true);
define('HTACCESS_BASE', '');

// Configurações de Sitemap
define('SITEMAP_PATH', 'sitemap.xml');
define('SITEMAP_URL', BLOG_URL . '/sitemap.xml');

// Configurações de Robots.txt
define('ROBOTS_PATH', 'robots.txt');
define('ROBOTS_URL', BLOG_URL . '/robots.txt');

// Configurações de Páginas
define('PAGES', [
    'sobre' => [
        'title' => 'Sobre Nós',
        'slug' => 'sobre',
        'url' => BLOG_URL . '/sobre'
    ],
    'contato' => [
        'title' => 'Contato',
        'slug' => 'contato',
        'url' => BLOG_URL . '/contato'
    ]
]);

// Configurações de Páginas Legais
define('LEGAL_PAGES', [
    'privacidade' => [
        'title' => 'Política de Privacidade',
        'slug' => 'privacidade',
        'url' => BLOG_URL . '/privacidade'
    ],
    'termos' => [
        'title' => 'Termos de Uso',
        'slug' => 'termos',
        'url' => BLOG_URL . '/termos'
    ]
]); 