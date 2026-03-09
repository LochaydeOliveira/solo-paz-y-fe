<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php'; // deve fornecer a variável $pdo
require_once '../includes/ConfigManager.php';
require_once 'includes/auth.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['error'] = 'Você não tem permissão para acessar esta página.';
    header('Location: index.php');
    exit;
}

try {
// Use PDO na instância do ConfigManager
$configManager = new ConfigManager($pdo);
$mensagem = '';
$tipo_mensagem = 'success';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $grupo = $_POST['grupo'] ?? 'geral';

    try {
        foreach ($_POST as $chave => $valor) {
            if ($chave !== 'submit' && $chave !== 'grupo') {
                $tipo = 'string';

                if (in_array($chave, ['posts_per_page'])) {
                    $tipo = 'integer';
                } elseif (in_array($chave, ['comments_active', 'newsletter_active'])) {
                    $tipo = 'boolean';
                }

                $configManager->set($chave, $valor, $tipo, $grupo);
            }
        }

        $mensagem = 'Configurações salvas com sucesso!';
        $tipo_mensagem = 'success';
    } catch (Exception $e) {
        $mensagem = 'Erro ao salvar configurações: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Obter configurações por grupo
$grupos = ['geral', 'seo', 'redes_sociais', 'integracao', 'paginas'];
$configuracoes = [];

foreach ($grupos as $grupo) {
    $configuracoes[$grupo] = $configManager->getGroup($grupo);
}

// Função auxiliar para obter valor de configuração
function getConfig($configs, $grupo, $chave, $padrao = '') {
    if (isset($configs[$grupo][$chave])) {
        return $configs[$grupo][$chave]['valor'];
    }
    return $padrao;
    }

} catch (Exception $e) {
    $mensagem = 'Erro ao carregar configurações: ' . $e->getMessage();
    $tipo_mensagem = 'danger';
    $configuracoes = [];
}

$page_title = 'Configurações';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cogs"></i> Configurações do Site
    </h1>
</div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                    <?= $mensagem ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Abas de Configurações -->
            <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral" type="button" role="tab">
                        <i class="fas fa-home"></i> Geral
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                        <i class="fas fa-search"></i> SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="redes-tab" data-bs-toggle="tab" data-bs-target="#redes" type="button" role="tab">
                        <i class="fab fa-facebook"></i> Redes Sociais
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="integracao-tab" data-bs-toggle="tab" data-bs-target="#integracao" type="button" role="tab">
                        <i class="fas fa-code"></i> Integração
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="paginas-tab" data-bs-toggle="tab" data-bs-target="#paginas" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Páginas
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="configTabsContent">
                <!-- Aba Geral -->
                <div class="tab-pane fade show active" id="geral" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-home"></i> Configurações Gerais
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="grupo" value="geral">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_title" class="form-label titles-form-adm">Título do Site</label>
                                            <input type="text" class="form-control" id="site_title" name="site_title" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'site_title', 'Brasil Hilário') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="admin_email" class="form-label titles-form-adm">Email do Administrador</label>
                                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'admin_email', 'admin@brasilhilario.com.br') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_url" class="form-label titles-form-adm">URL do Site</label>
                                            <input type="url" class="form-control" id="site_url" name="site_url" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'site_url', 'https://brasilhilario.com.br') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="posts_per_page" class="form-label titles-form-adm">Posts por Página</label>
                                            <input type="number" class="form-control" id="posts_per_page" name="posts_per_page" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'posts_per_page', '10') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="primary_color" class="form-label titles-form-adm">Cor Primária</label>
                                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'primary_color', '#0b8103') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="secondary_color" class="form-label titles-form-adm">Cor Secundária</label>
                                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'secondary_color', '#b30606') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logo_url" class="form-label titles-form-adm">URL do Logo</label>
                                            <input type="text" class="form-control" id="logo_url" name="logo_url" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'logo_url', 'assets/images/logo-brasil-hilario-quadrada-svg.svg') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="favicon_url" class="form-label titles-form-adm">URL do Favicon</label>
                                            <input type="text" class="form-control" id="favicon_url" name="favicon_url" 
                                                   value="<?= getConfig($configuracoes, 'geral', 'favicon_url', 'assets/images/favicon.ico') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label titles-form-adm">Descrição do Site</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?= getConfig($configuracoes, 'geral', 'site_description', 'O melhor do humor brasileiro') ?></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="comments_active" name="comments_active" value="1" 
                                           <?= getConfig($configuracoes, 'geral', 'comments_active', '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label titles-form-adm" for="comments_active">
                                        Comentários Ativos
                                    </label>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Configurações Gerais
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Aba SEO -->
                <div class="tab-pane fade" id="seo" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search"></i> Configurações SEO
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="grupo" value="seo">
                                
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label titles-form-adm">Palavras-chave (Meta Keywords)</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?= getConfig($configuracoes, 'seo', 'meta_keywords', 'humor, brasileiro, piadas, memes, comédia') ?>">
                                    <div class="form-text">Separe as palavras-chave por vírgula</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="og_image_default" class="form-label titles-form-adm">Imagem Padrão para Redes Sociais</label>
                                    <input type="text" class="form-control" id="og_image_default" name="og_image_default" 
                                           value="<?= getConfig($configuracoes, 'seo', 'og_image_default', 'assets/images/og-image-default.jpg') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="google_analytics_id" class="form-label titles-form-adm">ID do Google Analytics</label>
                                    <input type="text" class="form-control" id="google_analytics_id" name="google_analytics_id" 
                                           value="<?= getConfig($configuracoes, 'seo', 'google_analytics_id', '') ?>">
                                    <div class="form-text">Ex: G-XXXXXXXXXX ou UA-XXXXXXXXX-X</div>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Configurações SEO
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Aba Redes Sociais -->
                <div class="tab-pane fade" id="redes" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fab fa-facebook"></i> Redes Sociais
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="grupo" value="redes_sociais">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="facebook_url" class="form-label titles-form-adm">URL do Facebook</label>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'facebook_url', '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="instagram_url" class="form-label titles-form-adm">URL do Instagram</label>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'instagram_url', '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="twitter_url" class="form-label titles-form-adm">URL do Twitter</label>
                                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'twitter_url', '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="youtube_url" class="form-label titles-form-adm">URL do YouTube</label>
                                            <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'youtube_url', '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tiktok_url" class="form-label titles-form-adm">URL do TikTok</label>
                                            <input type="url" class="form-control" id="tiktok_url" name="tiktok_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'tiktok_url', '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telegram_url" class="form-label titles-form-adm">URL do Telegram</label>
                                            <input type="url" class="form-control" id="telegram_url" name="telegram_url" 
                                                   value="<?= getConfig($configuracoes, 'redes_sociais', 'telegram_url', '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Redes Sociais
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Aba Integração -->
                <div class="tab-pane fade" id="integracao" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-code"></i> Códigos de Integração
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="grupo" value="integracao">
                                
                                <div class="mb-3">
                                    <label for="head_code" class="form-label titles-form-adm">Código para &lt;head&gt;</label>
                                    <textarea class="form-control" id="head_code" name="head_code" rows="4" placeholder="Cole aqui códigos que devem ser inseridos no &lt;head&gt; da página"><?= getConfig($configuracoes, 'integracao', 'head_code', '') ?></textarea>
                                    <div class="form-text">Códigos como meta tags, scripts de analytics, etc.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="body_code" class="form-label titles-form-adm">Código para &lt;body&gt;</label>
                                    <textarea class="form-control" id="body_code" name="body_code" rows="4" placeholder="Cole aqui códigos que devem ser inseridos no final do &lt;body&gt;"><?= getConfig($configuracoes, 'integracao', 'body_code', '') ?></textarea>
                                    <div class="form-text">Códigos como widgets, scripts de chat, etc.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="adsense_code" class="form-label titles-form-adm">Código do Google AdSense</label>
                                    <textarea class="form-control" id="adsense_code" name="adsense_code" rows="4" placeholder="Cole aqui o código do Google AdSense"><?= getConfig($configuracoes, 'integracao', 'adsense_code', '') ?></textarea>
                                    <div class="form-text">Código de anúncios do Google AdSense</div>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Códigos de Integração
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Aba Páginas -->
                <div class="tab-pane fade" id="paginas" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-alt"></i> Configurações de Páginas
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="grupo" value="paginas">
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="newsletter_active" name="newsletter_active" value="1" 
                                           <?= getConfig($configuracoes, 'paginas', 'newsletter_active', '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label titles-form-adm" for="newsletter_active">
                                        Newsletter Ativa
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="newsletter_title" class="form-label titles-form-adm">Título da Newsletter</label>
                                    <input type="text" class="form-control" id="newsletter_title" name="newsletter_title" 
                                           value="<?= getConfig($configuracoes, 'paginas', 'newsletter_title', 'Inscreva-se na Newsletter') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="newsletter_description" class="form-label titles-form-adm">Descrição da Newsletter</label>
                                    <textarea class="form-control" id="newsletter_description" name="newsletter_description" rows="3"><?= getConfig($configuracoes, 'paginas', 'newsletter_description', 'Receba as melhores piadas e memes diretamente no seu email!') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="about_page_title" class="form-label titles-form-adm">Título da Página Sobre</label>
                                    <input type="text" class="form-control" id="about_page_title" name="about_page_title" 
                                           value="<?= getConfig($configuracoes, 'paginas', 'about_page_title', 'Sobre Nós') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_page_title" class="form-label titles-form-adm">Título da Página de Contato</label>
                                    <input type="text" class="form-control" id="contact_page_title" name="contact_page_title" 
                                           value="<?= getConfig($configuracoes, 'paginas', 'contact_page_title', 'Entre em Contato') ?>">
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Configurações de Páginas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


<?php include 'includes/footer.php'; ?>

<style>
.nav-tabs .nav-link {
    color: #495057!important;
    border: none;
    border-bottom: 6px solid transparent;
    padding: 0.75rem 1rem;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #495057;
    color: #495057;
}

.nav-tabs .nav-link.active {
    border-bottom-color:#0b8103;
    background-color: transparent;
    color: #0b8103;
}

.nav-tabs .nav-link i {
    margin-right: 0.5rem;
}

.form-control-color {
    width: 100%;
    height: 38px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card-header .card-title {
    color: #495057;
    font-weight: 600;
}

.card-header .card-title i {
    margin-right: 0.5rem;
    color: #0d6efd;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}
#configTabs {
    display: flex;
}
</style> 
