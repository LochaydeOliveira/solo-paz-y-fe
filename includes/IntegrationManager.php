<?php
require_once __DIR__ . '/db.php';

class IntegrationManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Gerenciar integrações
    public function getIntegracao($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM integracoes WHERE id = ? AND ativo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getIntegracoesPorPlataforma($plataforma) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM integracoes 
            WHERE plataforma = ? AND ativo = 1 
            ORDER BY nome
        ");
        $stmt->execute([$plataforma]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllIntegracoes() {
        $stmt = $this->pdo->query("
            SELECT * FROM integracoes WHERE ativo = 1 ORDER BY plataforma, nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createIntegracao($plataforma, $tipo, $nome, $chave_api = '', $chave_secreta = '', $configuracoes = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO integracoes (plataforma, tipo, nome, chave_api, chave_secreta, configuracoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$plataforma, $tipo, $nome, $chave_api, $chave_secreta, $configuracoes]);
    }
    
    public function updateIntegracao($id, $dados) {
        $stmt = $this->pdo->prepare("
            UPDATE integracoes 
            SET plataforma = ?, tipo = ?, nome = ?, chave_api = ?, chave_secreta = ?, 
                configuracoes = ?, ativo = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['plataforma'],
            $dados['tipo'],
            $dados['nome'],
            $dados['chave_api'],
            $dados['chave_secreta'],
            $dados['configuracoes'],
            $dados['ativo'],
            $id
        ]);
    }
    
    public function deleteIntegracao($id) {
        $stmt = $this->pdo->prepare("DELETE FROM integracoes WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Métodos específicos para cada plataforma
    public function getGoogleAnalytics() {
        return $this->getIntegracoesPorPlataforma('google');
    }
    
    public function getGoogleAdSense() {
        $integracao = $this->getIntegracoesPorPlataforma('google');
        foreach ($integracao as $item) {
            if ($item['tipo'] === 'ads') {
                return $item;
            }
        }
        return null;
    }
    
    public function getFacebookPixel() {
        $integracao = $this->getIntegracoesPorPlataforma('facebook');
        foreach ($integracao as $item) {
            if ($item['tipo'] === 'pixel') {
                return $item;
            }
        }
        return null;
    }
    
    // Gerar código de integração
    public function generateIntegrationCode($plataforma, $tipo) {
        $integracao = $this->getIntegracoesPorPlataforma($plataforma);
        foreach ($integracao as $item) {
            if ($item['tipo'] === $tipo) {
                return $this->generateCode($item);
            }
        }
        return '';
    }
    
    private function generateCode($integracao) {
        $config = json_decode($integracao['configuracoes'], true);
        
        switch ($integracao['plataforma']) {
            case 'google':
                return $this->generateGoogleCode($integracao, $config);
            case 'facebook':
                return $this->generateFacebookCode($integracao, $config);
            case 'twitter':
                return $this->generateTwitterCode($integracao, $config);
            default:
                return '';
        }
    }
    
    private function generateGoogleCode($integracao, $config) {
        switch ($integracao['tipo']) {
            case 'analytics':
                return $this->generateGoogleAnalyticsCode($integracao, $config);
            case 'ads':
                return $this->generateGoogleAdSenseCode($integracao, $config);
            default:
                return '';
        }
    }
    
    private function generateGoogleAnalyticsCode($integracao, $config) {
        if (empty($integracao['chave_api'])) return '';
        
        return "
        <!-- Google Analytics -->
        <script async src=\"https://www.googletagmanager.com/gtag/js?id={$integracao['chave_api']}\"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{$integracao['chave_api']}');
        </script>
        ";
    }
    
    private function generateGoogleAdSenseCode($integracao, $config) {
        if (empty($integracao['chave_api'])) return '';
        
        return "
        <!-- Google AdSense -->
        <script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={$integracao['chave_api']}\" crossorigin=\"anonymous\"></script>
        ";
    }
    
    private function generateFacebookCode($integracao, $config) {
        switch ($integracao['tipo']) {
            case 'pixel':
                return $this->generateFacebookPixelCode($integracao, $config);
            case 'social':
                return $this->generateFacebookSocialCode($integracao, $config);
            default:
                return '';
        }
    }
    
    private function generateFacebookPixelCode($integracao, $config) {
        if (empty($integracao['chave_api'])) return '';
        
        return "
        <!-- Facebook Pixel -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$integracao['chave_api']}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height=\"1\" width=\"1\" style=\"display:none\" 
                 src=\"https://www.facebook.com/tr?id={$integracao['chave_api']}&ev=PageView&noscript=1\"/>
        </noscript>
        ";
    }
    
    private function generateFacebookSocialCode($integracao, $config) {
        return "
        <!-- Facebook Social -->
        <div id=\"fb-root\"></div>
        <script async defer crossorigin=\"anonymous\" 
                src=\"https://connect.facebook.net/pt_BR/sdk.js\">
        </script>
        ";
    }
    
    private function generateTwitterCode($integracao, $config) {
        return "
        <!-- Twitter Widget -->
        <script async src=\"https://platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>
        ";
    }
    
    // Verificar status das integrações
    public function checkIntegrationStatus($id) {
        $integracao = $this->getIntegracao($id);
        if (!$integracao) return false;
        
        // Aqui você pode implementar verificações específicas
        // Por exemplo, verificar se as chaves API são válidas
        return !empty($integracao['chave_api']);
    }
} 