<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ConfigManager.php';

class SiteConfig {
    private static $instance = null;
    private $configManager;
    private $configs = [];
    private $loaded = false;

    private function __construct() {
        global $pdo; // Usa PDO agora
        $this->configManager = new ConfigManager($pdo);
        $this->loadConfigs();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfigs() {
        if (!$this->loaded) {
            $this->configs = $this->configManager->getAll();
            $this->loaded = true;
        }
    }

    public function get($chave, $padrao = null) {
        if (isset($this->configs[$chave])) {
            return $this->configs[$chave]['valor'];
        }
        return $this->configManager->get($chave, $padrao);
    }

    public function getGroup($grupo) {
        return $this->configManager->getGroup($grupo);
    }

    // Métodos específicos
    public function getSiteTitle() {
        return $this->get('site_title', 'Meu Site');
    }

    public function getSiteDescription() {
        return $this->get('site_description', 'Descrição do site');
    }

    public function getSiteUrl() {
        return $this->get('site_url', 'http://localhost');
    }

    public function getAdminEmail() {
        return $this->get('admin_email', 'admin@site.com');
    }

    public function getPostsPerPage() {
        return $this->get('posts_per_page', 10);
    }

    public function isCommentsActive() {
        return $this->get('comments_active', true);
    }

    public function getPrimaryColor() {
        return $this->get('primary_color', '#1a1a1a');
    }

    public function getSecondaryColor() {
        return $this->get('secondary_color', '#b30606');
    }

    public function getLogoUrl() {
        return $this->get('logo_url', '/assets/images/logo.png');
    }

    public function getFaviconUrl() {
        return $this->get('favicon_url', '/assets/images/favicon.ico');
    }

    public function getMetaKeywords() {
        return $this->get('meta_keywords', 'palavras, chave, site');
    }

    public function getOgImage() {
        return $this->get('og_image', '/assets/images/og-image.jpg');
    }

    public function getGoogleAnalyticsId() {
        return $this->get('google_analytics_id', '');
    }

    public function getSocialLinks() {
        return $this->get('social_links', []);
    }

    public function getIntegrationCodes() {
        return $this->get('integration_codes', []);
    }

    public function getPageConfigs() {
        return $this->get('page_configs', []);
    }

    public function isNewsletterActive() {
        return $this->get('newsletter_active', false);
    }

    public function getNewsletterTitle() {
        return $this->get('newsletter_title', 'Inscreva-se na Newsletter');
    }

    public function getNewsletterDescription() {
        return $this->get('newsletter_description', 'Receba as últimas notícias e atualizações');
    }

    public function generateCustomCSS() {
        $primaryColor = $this->getPrimaryColor();
        $secondaryColor = $this->getSecondaryColor();

        return "
        :root {
            --primary-color: {$primaryColor};
            --secondary-color: {$secondaryColor};
        }";
    }

    public function generateMetaTags($title = '', $description = '', $keywords = '') {
        $siteTitle = $this->getSiteTitle();
        $siteDescription = $this->getSiteDescription();
        $siteKeywords = $this->getMetaKeywords();
        $ogImage = $this->getOgImage();
        $siteUrl = $this->getSiteUrl();

        $finalTitle = $title ? $title . ' - ' . $siteTitle : $siteTitle;
        $finalDescription = $description ?: $siteDescription;
        $finalKeywords = $keywords ?: $siteKeywords;

        return "
        <title>{$finalTitle}</title>
        <meta name=\"description\" content=\"{$finalDescription}\">
        <meta name=\"keywords\" content=\"{$finalKeywords}\">

        <!-- Open Graph -->
        <meta property=\"og:title\" content=\"{$finalTitle}\">
        <meta property=\"og:description\" content=\"{$finalDescription}\">
        <meta property=\"og:image\" content=\"{$siteUrl}{$ogImage}\">
        <meta property=\"og:url\" content=\"{$siteUrl}\">
        <meta property=\"og:type\" content=\"website\">

        <!-- Twitter Card -->
        <meta name=\"twitter:card\" content=\"summary_large_image\">
        <meta name=\"twitter:title\" content=\"{$finalTitle}\">
        <meta name=\"twitter:description\" content=\"{$finalDescription}\">
        <meta name=\"twitter:image\" content=\"{$siteUrl}{$ogImage}\">";
    }

    public function generateHeadCodes() {
        $codes = [];
        $gaId = $this->getGoogleAnalyticsId();

        if ($gaId) {
            $codes[] = "
            <!-- Google Analytics -->
            <script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{$gaId}');
            </script>";
        }

        $integrationCodes = $this->getIntegrationCodes();
        if (isset($integrationCodes['head'])) {
            $codes[] = $integrationCodes['head'];
        }

        return implode("\n", $codes);
    }

    public function generateBodyCodes() {
        $codes = [];
        $integrationCodes = $this->getIntegrationCodes();

        if (isset($integrationCodes['body'])) {
            $codes[] = $integrationCodes['body'];
        }

        return implode("\n", $codes);
    }
}
