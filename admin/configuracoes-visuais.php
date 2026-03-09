<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/VisualConfigManager.php';
require_once 'includes/auth.php';

$visualConfig = new VisualConfigManager($pdo);
$mensagem = '';
$tipo_mensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $salvas = 0;
        $debug_info = [];
        
        foreach ($_POST as $chave => $valor) {
            if (strpos($chave, 'cor_') === 0 && !empty($valor)) {
                $partes = explode('_', $chave, 3);
                if (count($partes) >= 3) {
                    $elemento = $partes[1];
                    $propriedade = 'cor_' . $partes[2];
                    
                    $resultado = $visualConfig->setCor($elemento, $propriedade, $valor);
                    if ($resultado) {
                        $salvas++;
                        $debug_info[] = "✅ {$chave} -> {$elemento}.{$propriedade} = {$valor}";
                    } else {
                        $debug_info[] = "❌ Falha ao salvar {$chave}";
                    }
                }
            }
        }
        
        foreach ($_POST as $chave => $valor) {
            if (strpos($chave, 'fonte_') === 0 && !empty($valor)) {

                $elemento = substr($chave, 6);
                
                $resultado = $visualConfig->setFonte($elemento, 'fonte', $valor);
                if ($resultado) {
                    $salvas++;
                    $debug_info[] = "✅ {$chave} -> {$elemento}.fonte = {$valor}";
                } else {
                    $debug_info[] = "❌ Falha ao salvar {$chave}";
                }
            }
        }
        

        if (isset($_POST['fonte_geral']) && !empty($_POST['fonte_geral'])) {
            $resultado = $visualConfig->setFonteGeral($_POST['fonte_geral']);
            if ($resultado) {
                $salvas++;
                $debug_info[] = "✅ fonte_geral -> site.fonte_geral = {$_POST['fonte_geral']}";
            } else {
                $debug_info[] = "❌ Falha ao salvar fonte_geral";
            }
        }
        

        if (isset($_POST['personalizar_fontes'])) {
            $resultado = $visualConfig->setPersonalizarFontes(true);
            if ($resultado) {
                $salvas++;
                $debug_info[] = "✅ personalizar_fontes -> site.personalizar_fontes = 1";
            } else {
                $debug_info[] = "❌ Falha ao salvar personalizar_fontes";
            }
        } else {
            $resultado = $visualConfig->setPersonalizarFontes(false);
            if ($resultado) {
                $salvas++;
                $debug_info[] = "✅ personalizar_fontes -> site.personalizar_fontes = 0";
            } else {
                $debug_info[] = "❌ Falha ao salvar personalizar_fontes";
            }
        }
        

        foreach ($_POST as $chave => $valor) {
            if (strpos($chave, 'peso_') === 0 && !empty($valor)) {
                $elemento = substr($chave, 5);
                
                $resultado = $visualConfig->setPesoFonte($elemento, $valor);
                if ($resultado) {
                    $salvas++;
                    $debug_info[] = "✅ {$chave} -> site.peso_{$elemento} = {$valor}";
                } else {
                    $debug_info[] = "❌ Falha ao salvar {$chave}";
                }
            }
        }
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'tamanho_') === 0) {
                $parts = explode('_', $key);
                if (count($parts) >= 3) {
                    $elemento = $parts[1];
                    $dispositivo = $parts[2];
                    $visualConfig->setTamanhoFonte($elemento, $dispositivo, $value);
                }
            }
        }
        

        if (isset($_POST['fonte_leia_tambem'])) {
            $visualConfig->setFonteSecao('leia_tambem', $_POST['fonte_leia_tambem']);
        }
        if (isset($_POST['peso_titulo_leia_tambem'])) {
            $visualConfig->setPesoSecao('leia_tambem', 'titulo', $_POST['peso_titulo_leia_tambem']);
        }
        if (isset($_POST['peso_texto_leia_tambem'])) {
            $visualConfig->setPesoSecao('leia_tambem', 'texto', $_POST['peso_texto_leia_tambem']);
        }
        if (isset($_POST['tamanho_titulo_leia_tambem_desktop'])) {
            $visualConfig->setTamanhoSecao('leia_tambem', 'titulo', 'desktop', $_POST['tamanho_titulo_leia_tambem_desktop']);
        }
        if (isset($_POST['tamanho_titulo_leia_tambem_mobile'])) {
            $visualConfig->setTamanhoSecao('leia_tambem', 'titulo', 'mobile', $_POST['tamanho_titulo_leia_tambem_mobile']);
        }
        if (isset($_POST['tamanho_texto_leia_tambem_desktop'])) {
            $visualConfig->setTamanhoSecao('leia_tambem', 'texto', 'desktop', $_POST['tamanho_texto_leia_tambem_desktop']);
        }
        if (isset($_POST['tamanho_texto_leia_tambem_mobile'])) {
            $visualConfig->setTamanhoSecao('leia_tambem', 'texto', 'mobile', $_POST['tamanho_texto_leia_tambem_mobile']);
        }
        

        if (isset($_POST['fonte_ultimas_portal'])) {
            $visualConfig->setFonteSecao('ultimas_portal', $_POST['fonte_ultimas_portal']);
        }
        if (isset($_POST['peso_titulo_ultimas_portal'])) {
            $visualConfig->setPesoSecao('ultimas_portal', 'titulo', $_POST['peso_titulo_ultimas_portal']);
        }
        if (isset($_POST['peso_texto_ultimas_portal'])) {
            $visualConfig->setPesoSecao('ultimas_portal', 'texto', $_POST['peso_texto_ultimas_portal']);
        }
        if (isset($_POST['tamanho_titulo_ultimas_portal_desktop'])) {
            $visualConfig->setTamanhoSecao('ultimas_portal', 'titulo', 'desktop', $_POST['tamanho_titulo_ultimas_portal_desktop']);
        }
        if (isset($_POST['tamanho_titulo_ultimas_portal_mobile'])) {
            $visualConfig->setTamanhoSecao('ultimas_portal', 'titulo', 'mobile', $_POST['tamanho_titulo_ultimas_portal_mobile']);
        }
        if (isset($_POST['tamanho_texto_ultimas_portal_desktop'])) {
            $visualConfig->setTamanhoSecao('ultimas_portal', 'texto', 'desktop', $_POST['tamanho_texto_ultimas_portal_desktop']);
        }
        if (isset($_POST['tamanho_texto_ultimas_portal_mobile'])) {
            $visualConfig->setTamanhoSecao('ultimas_portal', 'texto', 'mobile', $_POST['tamanho_texto_ultimas_portal_mobile']);
        }
        

        if (isset($_POST['fonte_titulo_conteudo'])) {
            $visualConfig->setFonte('titulo_conteudo', 'fonte', $_POST['fonte_titulo_conteudo']);
        }
        if (isset($_POST['peso_titulo_conteudo'])) {
            $visualConfig->setPesoFonte('titulo_conteudo', $_POST['peso_titulo_conteudo']);
        }
        if (isset($_POST['tamanho_h1_desktop'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h1', 'desktop', $_POST['tamanho_h1_desktop']);
        }
        if (isset($_POST['tamanho_h1_mobile'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h1', 'mobile', $_POST['tamanho_h1_mobile']);
        }
        if (isset($_POST['tamanho_h2_desktop'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h2', 'desktop', $_POST['tamanho_h2_desktop']);
        }
        if (isset($_POST['tamanho_h2_mobile'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h2', 'mobile', $_POST['tamanho_h2_mobile']);
        }
        if (isset($_POST['tamanho_h3_desktop'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h3', 'desktop', $_POST['tamanho_h3_desktop']);
        }
        if (isset($_POST['tamanho_h3_mobile'])) {
            $visualConfig->setTamanhoFonte('titulo_conteudo_h3', 'mobile', $_POST['tamanho_h3_mobile']);
        }
        

        
        $mensagem = "Configurações visuais salvas com sucesso! ({$salvas} configurações atualizadas)";

        $tipo_mensagem = 'success';
        
        if (!empty($debug_info)) {
            $mensagem .= "\n\nDebug:\n" . implode("\n", $debug_info);
        }
        
    } catch (Exception $e) {
        $mensagem = 'Erro ao salvar configurações: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$configs = $visualConfig->getAllConfigs();

if (empty($configs['fontes']['site'])) {
    $visualConfig->setFonte('site', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif');
    $visualConfig->setFonte('titulo', 'fonte', 'Merriweather, serif');
    $visualConfig->setFonte('paragrafo', 'fonte', 'Inter, sans-serif');
    $configs = $visualConfig->getAllConfigs();
    
}

$page_title = 'Configurações Visuais';
include 'includes/header.php';
?>

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
            width: 6%;
            height: 40px;
            border-radius: 0;
            border: none!important;
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
        #visualTabs {
            display: flex;
        }

    </style>
</head>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-palette"></i> Configurações Visuais
    </h1>
</div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                    <pre style="white-space: pre-wrap; margin: 0;"><?= htmlspecialchars($mensagem) ?></pre>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <ul class="nav nav-tabs mb-4" id="visualTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cores-tab" data-bs-toggle="tab" data-bs-target="#cores" type="button" role="tab">
                        <i class="fas fa-palette"></i> Cores
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fontes-tab" data-bs-toggle="tab" data-bs-target="#fontes" type="button" role="tab">
                        <i class="fas fa-font"></i> Fontes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="blog-specific-tab" data-bs-toggle="tab" data-bs-target="#blog-specific" type="button" role="tab">
                        <i class="fas fa-newspaper"></i> Blog Específico
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </li>
            </ul>
            
            <form method="POST">
                <input type="hidden" name="submit" value="1">
                

                <div class="tab-content" id="visualTabsContent">
                    <div class="tab-pane fade show active" id="cores" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Cores Principais</h4>
                                <div class="mb-3">
                                    <label class="form-label">Cor Primária</label>
                                    <input type="color" class="form-control form-control-color" name="cor_site_primaria" 
                                           value="<?= $configs['cores']['site']['cor_primaria'] ?? '#007bff' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor Secundária</label>
                                    <input type="color" class="form-control form-control-color" name="cor_site_secundaria" 
                                           value="<?= $configs['cores']['site']['cor_secundaria'] ?? '#6c757d' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Sucesso</label>
                                    <input type="color" class="form-control form-control-color" name="cor_site_sucesso" 
                                           value="<?= $configs['cores']['site']['cor_sucesso'] ?? '#28a745' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Perigo</label>
                                    <input type="color" class="form-control form-control-color" name="cor_site_perigo" 
                                           value="<?= $configs['cores']['site']['cor_perigo'] ?? '#dc3545' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Cores do Header</h4>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Fundo</label>
                                    <input type="color" class="form-control form-control-color" name="cor_header_fundo" 
                                           value="<?= $configs['cores']['header']['cor_fundo'] ?? '#ffffff' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor do Texto</label>
                                    <input type="color" class="form-control form-control-color" name="cor_header_texto" 
                                           value="<?= $configs['cores']['header']['cor_texto'] ?? '#333333' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor dos Links</label>
                                    <input type="color" class="form-control form-control-color" name="cor_header_link" 
                                           value="<?= $configs['cores']['header']['cor_link'] ?? '#007bff' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor dos Links (Hover)</label>
                                    <input type="color" class="form-control form-control-color" name="cor_header_link_hover" 
                                           value="<?= $configs['cores']['header']['cor_link_hover'] ?? '#0056b3' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h4>Cores do Footer</h4>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Fundo</label>
                                    <input type="color" class="form-control form-control-color" name="cor_footer_fundo" 
                                           value="<?= $configs['cores']['footer']['cor_fundo'] ?? '#f8f9fa' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor do Texto</label>
                                    <input type="color" class="form-control form-control-color" name="cor_footer_texto" 
                                           value="<?= $configs['cores']['footer']['cor_texto'] ?? '#6c757d' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor dos Links</label>
                                    <input type="color" class="form-control form-control-color" name="cor_footer_link" 
                                           value="<?= $configs['cores']['footer']['cor_link'] ?? '#007bff' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Cores dos Botões</h4>
                                <div class="mb-3">
                                    <label class="form-label">Cor Primária</label>
                                    <input type="color" class="form-control form-control-color" name="cor_botao_primario" 
                                           value="<?= $configs['cores']['botao']['cor_primario'] ?? '#007bff' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor Secundária</label>
                                    <input type="color" class="form-control form-control-color" name="cor_botao_secundario" 
                                           value="<?= $configs['cores']['botao']['cor_secundario'] ?? '#6c757d' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Sucesso</label>
                                    <input type="color" class="form-control form-control-color" name="cor_botao_sucesso" 
                                           value="<?= $configs['cores']['botao']['cor_sucesso'] ?? '#28a745' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Cores dos Cards</h4>
                                <div class="mb-3">
                                    <label class="form-label">Cor de Fundo</label>
                                    <input type="color" class="form-control form-control-color" name="cor_card_fundo" 
                                           value="<?= $configs['cores']['card']['cor_fundo'] ?? '#ffffff' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor da Borda</label>
                                    <input type="color" class="form-control form-control-color" name="cor_card_borda" 
                                           value="<?= $configs['cores']['card']['cor_borda'] ?? '#dee2e6' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor do Texto</label>
                                    <input type="color" class="form-control form-control-color" name="cor_card_texto" 
                                           value="<?= $configs['cores']['card']['cor_texto'] ?? '#212529' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="fontes" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-cog"></i> Configuração de Fontes</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Fonte Geral do Site -->
                                        <div class="mb-4">
                                            <h6>Fonte Geral do Site</h6>
                                            <p class="text-muted">Esta fonte será aplicada em todo o site quando a personalização estiver desabilitada.</p>
                                            <div class="mb-3">
                                                <label class="form-label">Fonte Geral</label>
                                                <select class="form-select" name="fonte_geral" id="fonte_geral">
                                                    <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                    <option value="Arial, sans-serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                    <option value="Helvetica, sans-serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                    <option value="Georgia, serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                    <option value="Times New Roman, serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                    <option value="Courier New, monospace" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Courier New, monospace' ? 'selected' : '' ?>>Courier New</option>
                                                    <option value="Merriweather, serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                    <option value="Inter, sans-serif" <?= ($configs['fontes']['site']['fonte_geral'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="personalizar_fontes" id="personalizar_fontes" value="1" 
                                                       <?= ($configs['fontes']['site']['personalizar_fontes'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="personalizar_fontes">
                                                    <strong>Personalizar Fontes do Site</strong>
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Quando habilitado, você pode definir fontes específicas para cada elemento do site.
                                                </small>
                                            </div>
                                        </div>

                                        
                                        <div id="fontes_personalizadas" style="display: none;">
                                            <hr>
                                            <h6>Fontes Personalizadas por Elemento</h6>
                                            <p class="text-muted">Configure fontes específicas para cada elemento do site.</p>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte dos Títulos</label>
                                                        <select class="form-select" name="fonte_titulos">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['titulos']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte dos Parágrafos</label>
                                                        <select class="form-select" name="fonte_paragrafos">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['paragrafos']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte da Navegação</label>
                                                        <select class="form-select" name="fonte_navegacao">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['navegacao']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte da Sidebar</label>
                                                        <select class="form-select" name="fonte_sidebar">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['sidebar']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte dos Cards</label>
                                                        <select class="form-select" name="fonte_cards">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['cards']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Fonte dos Botões</label>
                                                        <select class="form-select" name="fonte_botoes">
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Merriweather, serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Merriweather, serif' ? 'selected' : '' ?>>Merriweather</option>
                                                            <option value="Inter, sans-serif" <?= ($configs['fontes']['botoes']['fonte'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' ?>>Inter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-bold"></i> Peso das Fontes</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Configure o peso (espessura) das fontes para cada elemento.</p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Peso dos Títulos</label>
                                            <select class="form-select" name="peso_titulos">
                                                <option value="100" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                <option value="300" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                <option value="400" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '400' ? 'selected' : '' ?>>400 - Normal</option>
                                                <option value="500" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                <option value="600" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                <option value="700" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                                <option value="900" <?= ($configs['fontes']['site']['peso_titulos'] ?? '700') === '900' ? 'selected' : '' ?>>900 - Black</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Peso dos Parágrafos</label>
                                            <select class="form-select" name="peso_paragrafos">
                                                <option value="100" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                <option value="300" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                <option value="400" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '400' ? 'selected' : '' ?>>400 - Normal</option>
                                                <option value="500" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                <option value="600" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                <option value="700" <?= ($configs['fontes']['site']['peso_paragrafos'] ?? '400') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Peso da Navegação</label>
                                            <select class="form-select" name="peso_navegacao">
                                                <option value="100" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                <option value="300" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                <option value="400" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '400' ? 'selected' : '' ?>>400 - Normal</option>
                                                <option value="500" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                <option value="600" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                <option value="700" <?= ($configs['fontes']['site']['peso_navegacao'] ?? '500') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Tamanhos Responsivos</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Configure tamanhos de fonte para desktop e mobile.</p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tamanho dos Títulos (Desktop)</label>
                                            <select class="form-select" name="tamanho_titulos_desktop">
                                                <option value="32px" <?= ($configs['fontes']['titulos']['tamanho_desktop'] ?? '28px') === '32px' ? 'selected' : '' ?>>32px</option>
                                                <option value="28px" <?= ($configs['fontes']['titulos']['tamanho_desktop'] ?? '28px') === '28px' ? 'selected' : '' ?>>28px</option>
                                                <option value="24px" <?= ($configs['fontes']['titulos']['tamanho_desktop'] ?? '28px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                <option value="20px" <?= ($configs['fontes']['titulos']['tamanho_desktop'] ?? '28px') === '20px' ? 'selected' : '' ?>>20px</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tamanho dos Títulos (Mobile)</label>
                                            <select class="form-select" name="tamanho_titulos_mobile">
                                                <option value="28px" <?= ($configs['fontes']['titulos']['tamanho_mobile'] ?? '24px') === '28px' ? 'selected' : '' ?>>28px</option>
                                                <option value="24px" <?= ($configs['fontes']['titulos']['tamanho_mobile'] ?? '24px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                <option value="20px" <?= ($configs['fontes']['titulos']['tamanho_mobile'] ?? '24px') === '20px' ? 'selected' : '' ?>>20px</option>
                                                <option value="18px" <?= ($configs['fontes']['titulos']['tamanho_mobile'] ?? '24px') === '18px' ? 'selected' : '' ?>>18px</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tamanho dos Parágrafos (Desktop)</label>
                                            <select class="form-select" name="tamanho_paragrafos_desktop">
                                                <option value="18px" <?= ($configs['fontes']['paragrafos']['tamanho_desktop'] ?? '16px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                <option value="16px" <?= ($configs['fontes']['paragrafos']['tamanho_desktop'] ?? '16px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                <option value="14px" <?= ($configs['fontes']['paragrafos']['tamanho_desktop'] ?? '16px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                <option value="12px" <?= ($configs['fontes']['paragrafos']['tamanho_desktop'] ?? '16px') === '12px' ? 'selected' : '' ?>>12px</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tamanho dos Parágrafos (Mobile)</label>
                                            <select class="form-select" name="tamanho_paragrafos_mobile">
                                                <option value="16px" <?= ($configs['fontes']['paragrafos']['tamanho_mobile'] ?? '14px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                <option value="14px" <?= ($configs['fontes']['paragrafos']['tamanho_mobile'] ?? '14px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                <option value="12px" <?= ($configs['fontes']['paragrafos']['tamanho_mobile'] ?? '14px') === '12px' ? 'selected' : '' ?>>12px</option>
                                                <option value="10px" <?= ($configs['fontes']['paragrafos']['tamanho_mobile'] ?? '14px') === '10px' ? 'selected' : '' ?>>10px</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    <div class="tab-pane fade" id="blog-specific" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cog"></i> Configuração de Fontes para Seções Específicas do Blog</h5>
                            </div>
                            <div class="card-body">
 
                                <div class="mb-4">
                                    <h6>Seção "Leia Também"</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fonte_leia_tambem" class="form-label">Fonte:</label>
                                                <select class="form-select" id="fonte_leia_tambem" name="fonte_leia_tambem" data-preview=".related-posts-title">
                                                    <option value="Arial, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                    <option value="Helvetica, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                    <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                    <option value="Georgia, serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                    <option value="Times New Roman, serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                    <option value="Verdana, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Verdana, sans-serif' ? 'selected' : '' ?>>Verdana</option>
                                                    <option value="Tahoma, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Tahoma, sans-serif' ? 'selected' : '' ?>>Tahoma</option>
                                                    <option value="Trebuchet MS, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Trebuchet MS, sans-serif' ? 'selected' : '' ?>>Trebuchet MS</option>
                                                    <option value="Impact, sans-serif" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Impact, sans-serif' ? 'selected' : '' ?>>Impact</option>
                                                    <option value="Comic Sans MS, cursive" <?= ($configs['fontes']['leia_tambem']['fonte'] ?? '') === 'Comic Sans MS, cursive' ? 'selected' : '' ?>>Comic Sans MS</option>
                                                </select>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="peso_titulo_leia_tambem" class="form-label">Peso do Título:</label>
                                                    <select class="form-select" id="peso_titulo_leia_tambem" name="peso_titulo_leia_tambem">
                                                        <option value="100" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                        <option value="200" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '200' ? 'selected' : '' ?>>200 - Extra Light</option>
                                                        <option value="300" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                        <option value="400" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '400' ? 'selected' : '' ?>>400 - Regular</option>
                                                        <option value="500" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                        <option value="600" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                        <option value="700" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                                        <option value="800" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '800' ? 'selected' : '' ?>>800 - Extra Bold</option>
                                                        <option value="900" <?= ($configs['fontes']['leia_tambem']['peso_titulo'] ?? '700') === '900' ? 'selected' : '' ?>>900 - Black</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label for="peso_texto_leia_tambem" class="form-label">Peso do Texto:</label>
                                                    <select class="form-select" id="peso_texto_leia_tambem" name="peso_texto_leia_tambem">
                                                        <option value="100" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                        <option value="200" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '200' ? 'selected' : '' ?>>200 - Extra Light</option>
                                                        <option value="300" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                        <option value="400" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '400' ? 'selected' : '' ?>>400 - Regular</option>
                                                        <option value="500" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                        <option value="600" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                        <option value="700" <?= ($configs['fontes']['leia_tambem']['peso_texto'] ?? '400') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <label for="tamanho_titulo_leia_tambem_desktop" class="form-label">Tamanho Título (Desktop):</label>
                                                    <select class="form-select" id="tamanho_titulo_leia_tambem_desktop" name="tamanho_titulo_leia_tambem_desktop">
                                                        <option value="18px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                        <option value="20px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '20px' ? 'selected' : '' ?>>20px</option>
                                                        <option value="22px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '22px' ? 'selected' : '' ?>>22px</option>
                                                        <option value="24px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                        <option value="26px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '26px' ? 'selected' : '' ?>>26px</option>
                                                        <option value="28px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_desktop'] ?? '20px') === '28px' ? 'selected' : '' ?>>28px</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label for="tamanho_titulo_leia_tambem_mobile" class="form-label">Tamanho Título (Mobile):</label>
                                                    <select class="form-select" id="tamanho_titulo_leia_tambem_mobile" name="tamanho_titulo_leia_tambem_mobile">
                                                        <option value="16px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_mobile'] ?? '18px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                        <option value="18px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_mobile'] ?? '18px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                        <option value="20px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_mobile'] ?? '18px') === '20px' ? 'selected' : '' ?>>20px</option>
                                                        <option value="22px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_mobile'] ?? '18px') === '22px' ? 'selected' : '' ?>>22px</option>
                                                        <option value="24px" <?= ($configs['fontes']['leia_tambem']['tamanho_titulo_mobile'] ?? '18px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <label for="tamanho_texto_leia_tambem_desktop" class="form-label">Tamanho Texto (Desktop):</label>
                                                    <select class="form-select" id="tamanho_texto_leia_tambem_desktop" name="tamanho_texto_leia_tambem_desktop">
                                                        <option value="12px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_desktop'] ?? '14px') === '12px' ? 'selected' : '' ?>>12px</option>
                                                        <option value="14px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_desktop'] ?? '14px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                        <option value="16px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_desktop'] ?? '14px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                        <option value="18px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_desktop'] ?? '14px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label for="tamanho_texto_leia_tambem_mobile" class="form-label">Tamanho Texto (Mobile):</label>
                                                    <select class="form-select" id="tamanho_texto_leia_tambem_mobile" name="tamanho_texto_leia_tambem_mobile">
                                                        <option value="10px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_mobile'] ?? '12px') === '10px' ? 'selected' : '' ?>>10px</option>
                                                        <option value="12px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_mobile'] ?? '12px') === '12px' ? 'selected' : '' ?>>12px</option>
                                                        <option value="14px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_mobile'] ?? '12px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                        <option value="16px" <?= ($configs['fontes']['leia_tambem']['tamanho_texto_mobile'] ?? '12px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 3rem;" class="mb-4">
                                            <h6>Seção "Últimas do Portal"</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="fonte_ultimas_portal" class="form-label">Fonte:</label>
                                                        <select class="form-select" id="fonte_ultimas_portal" name="fonte_ultimas_portal" data-preview=".related-posts-title">
                                                            <option value="Arial, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                            <option value="Helvetica, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : '' ?>>Helvetica</option>
                                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                                            <option value="Georgia, serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                                            <option value="Times New Roman, serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                                            <option value="Verdana, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Verdana, sans-serif' ? 'selected' : '' ?>>Verdana</option>
                                                            <option value="Tahoma, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Tahoma, sans-serif' ? 'selected' : '' ?>>Tahoma</option>
                                                            <option value="Trebuchet MS, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Trebuchet MS, sans-serif' ? 'selected' : '' ?>>Trebuchet MS</option>
                                                            <option value="Impact, sans-serif" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Impact, sans-serif' ? 'selected' : '' ?>>Impact</option>
                                                            <option value="Comic Sans MS, cursive" <?= ($configs['fontes']['ultimas_portal']['fonte'] ?? '') === 'Comic Sans MS, cursive' ? 'selected' : '' ?>>Comic Sans MS</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <label for="peso_titulo_ultimas_portal" class="form-label">Peso do Título:</label>
                                                            <select class="form-select" id="peso_titulo_ultimas_portal" name="peso_titulo_ultimas_portal">
                                                                <option value="100" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                                <option value="200" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '200' ? 'selected' : '' ?>>200 - Extra Light</option>
                                                                <option value="300" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                                <option value="400" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '400' ? 'selected' : '' ?>>400 - Regular</option>
                                                                <option value="500" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                                <option value="600" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                                <option value="700" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                                                <option value="800" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '800' ? 'selected' : '' ?>>800 - Extra Bold</option>
                                                                <option value="900" <?= ($configs['fontes']['ultimas_portal']['peso_titulo'] ?? '700') === '900' ? 'selected' : '' ?>>900 - Black</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label for="peso_texto_ultimas_portal" class="form-label">Peso do Texto:</label>
                                                            <select class="form-select" id="peso_texto_ultimas_portal" name="peso_texto_ultimas_portal">
                                                                <option value="100" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '100' ? 'selected' : '' ?>>100 - Thin</option>
                                                                <option value="200" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '200' ? 'selected' : '' ?>>200 - Extra Light</option>
                                                                <option value="300" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '300' ? 'selected' : '' ?>>300 - Light</option>
                                                                <option value="400" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '400' ? 'selected' : '' ?>>400 - Regular</option>
                                                                <option value="500" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                                                <option value="600" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                                                <option value="700" <?= ($configs['fontes']['ultimas_portal']['peso_texto'] ?? '400') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <label for="tamanho_titulo_ultimas_portal_desktop" class="form-label">Tamanho Título (Desktop):</label>
                                                            <select class="form-select" id="tamanho_titulo_ultimas_portal_desktop" name="tamanho_titulo_ultimas_portal_desktop">
                                                                <option value="18px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                                <option value="20px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '20px' ? 'selected' : '' ?>>20px</option>
                                                                <option value="22px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '22px' ? 'selected' : '' ?>>22px</option>
                                                                <option value="24px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                                <option value="26px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '26px' ? 'selected' : '' ?>>26px</option>
                                                                <option value="28px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_desktop'] ?? '20px') === '28px' ? 'selected' : '' ?>>28px</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label for="tamanho_titulo_ultimas_portal_mobile" class="form-label">Tamanho Título (Mobile):</label>
                                                            <select class="form-select" id="tamanho_titulo_ultimas_portal_mobile" name="tamanho_titulo_ultimas_portal_mobile">
                                                                <option value="16px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_mobile'] ?? '18px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                                <option value="18px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_mobile'] ?? '18px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                                <option value="20px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_mobile'] ?? '18px') === '20px' ? 'selected' : '' ?>>20px</option>
                                                                <option value="22px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_mobile'] ?? '18px') === '22px' ? 'selected' : '' ?>>22px</option>
                                                                <option value="24px" <?= ($configs['fontes']['ultimas_portal']['tamanho_titulo_mobile'] ?? '18px') === '24px' ? 'selected' : '' ?>>24px</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <label for="tamanho_texto_ultimas_portal_desktop" class="form-label">Tamanho Texto (Desktop):</label>
                                                            <select class="form-select" id="tamanho_texto_ultimas_portal_desktop" name="tamanho_texto_ultimas_portal_desktop">
                                                                <option value="12px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_desktop'] ?? '14px') === '12px' ? 'selected' : '' ?>>12px</option>
                                                                <option value="14px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_desktop'] ?? '14px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                                <option value="16px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_desktop'] ?? '14px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                                <option value="18px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_desktop'] ?? '14px') === '18px' ? 'selected' : '' ?>>18px</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label for="tamanho_texto_ultimas_portal_mobile" class="form-label">Tamanho Texto (Mobile):</label>
                                                            <select class="form-select" id="tamanho_texto_ultimas_portal_mobile" name="tamanho_texto_ultimas_portal_mobile">
                                                                <option value="10px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_mobile'] ?? '12px') === '10px' ? 'selected' : '' ?>>10px</option>
                                                                <option value="12px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_mobile'] ?? '12px') === '12px' ? 'selected' : '' ?>>12px</option>
                                                                <option value="14px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_mobile'] ?? '12px') === '14px' ? 'selected' : '' ?>>14px</option>
                                                                <option value="16px" <?= ($configs['fontes']['ultimas_portal']['tamanho_texto_mobile'] ?? '12px') === '16px' ? 'selected' : '' ?>>16px</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-heading"></i> 
                                    Títulos de Conteúdo (H1, H2, H3, H4, H5, H6)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="fonte_titulo_conteudo" class="form-label">Fonte dos Títulos:</label>
                                        <select class="form-select" id="fonte_titulo_conteudo" name="fonte_titulo_conteudo">
                                            <option value="Segoe UI, Tahoma, Geneva, Verdana, sans-serif" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ? 'selected' : '' ?>>Segoe UI</option>
                                            <option value="Arial, Helvetica, sans-serif" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Arial, Helvetica, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                            <option value="Georgia, serif" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                                            <option value="Times New Roman, Times, serif" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Times New Roman, Times, serif' ? 'selected' : '' ?>>Times New Roman</option>
                                            <option value="Verdana, Geneva, sans-serif" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Verdana, Geneva, sans-serif' ? 'selected' : '' ?>>Verdana</option>
                                            <option value="Courier New, Courier, monospace" <?= ($configs['fontes']['titulo_conteudo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif') === 'Courier New, Courier, monospace' ? 'selected' : '' ?>>Courier New</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="peso_titulo_conteudo" class="form-label">Peso da Fonte:</label>
                                        <select class="form-select" id="peso_titulo_conteudo" name="peso_titulo_conteudo">
                                            <option value="400" <?= ($configs['fontes']['titulo_conteudo']['peso'] ?? '600') === '400' ? 'selected' : '' ?>>400 - Regular</option>
                                            <option value="500" <?= ($configs['fontes']['titulo_conteudo']['peso'] ?? '600') === '500' ? 'selected' : '' ?>>500 - Medium</option>
                                            <option value="600" <?= ($configs['fontes']['titulo_conteudo']['peso'] ?? '600') === '600' ? 'selected' : '' ?>>600 - Semi Bold</option>
                                            <option value="700" <?= ($configs['fontes']['titulo_conteudo']['peso'] ?? '600') === '700' ? 'selected' : '' ?>>700 - Bold</option>
                                            <option value="800" <?= ($configs['fontes']['titulo_conteudo']['peso'] ?? '600') === '800' ? 'selected' : '' ?>>800 - Extra Bold</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label for="tamanho_h1_desktop" class="form-label">H1 (Desktop):</label>
                                        <select class="form-select" id="tamanho_h1_desktop" name="tamanho_h1_desktop">
                                            <option value="32px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_desktop'] ?? '32px') === '32px' ? 'selected' : '' ?>>32px</option>
                                            <option value="36px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_desktop'] ?? '32px') === '36px' ? 'selected' : '' ?>>36px</option>
                                            <option value="40px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_desktop'] ?? '32px') === '40px' ? 'selected' : '' ?>>40px</option>
                                            <option value="44px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_desktop'] ?? '32px') === '44px' ? 'selected' : '' ?>>44px</option>
                                            <option value="48px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_desktop'] ?? '32px') === '48px' ? 'selected' : '' ?>>48px</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tamanho_h2_desktop" class="form-label">H2 (Desktop):</label>
                                        <select class="form-select" id="tamanho_h2_desktop" name="tamanho_h2_desktop">
                                            <option value="28px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_desktop'] ?? '28px') === '28px' ? 'selected' : '' ?>>28px</option>
                                            <option value="30px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_desktop'] ?? '28px') === '30px' ? 'selected' : '' ?>>30px</option>
                                            <option value="32px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_desktop'] ?? '28px') === '32px' ? 'selected' : '' ?>>32px</option>
                                            <option value="36px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_desktop'] ?? '28px') === '36px' ? 'selected' : '' ?>>36px</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tamanho_h3_desktop" class="form-label">H3 (Desktop):</label>
                                        <select class="form-select" id="tamanho_h3_desktop" name="tamanho_h3_desktop">
                                            <option value="24px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_desktop'] ?? '24px') === '24px' ? 'selected' : '' ?>>24px</option>
                                            <option value="26px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_desktop'] ?? '24px') === '26px' ? 'selected' : '' ?>>26px</option>
                                            <option value="28px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_desktop'] ?? '24px') === '28px' ? 'selected' : '' ?>>28px</option>
                                            <option value="30px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_desktop'] ?? '24px') === '30px' ? 'selected' : '' ?>>30px</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label for="tamanho_h1_mobile" class="form-label">H1 (Mobile):</label>
                                        <select class="form-select" id="tamanho_h1_mobile" name="tamanho_h1_mobile">
                                            <option value="28px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_mobile'] ?? '28px') === '28px' ? 'selected' : '' ?>>28px</option>
                                            <option value="30px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_mobile'] ?? '28px') === '30px' ? 'selected' : '' ?>>30px</option>
                                            <option value="32px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_mobile'] ?? '28px') === '32px' ? 'selected' : '' ?>>32px</option>
                                            <option value="36px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h1_mobile'] ?? '28px') === '36px' ? 'selected' : '' ?>>36px</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tamanho_h2_mobile" class="form-label">H2 (Mobile):</label>
                                        <select class="form-select" id="tamanho_h2_mobile" name="tamanho_h2_mobile">
                                            <option value="24px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_mobile'] ?? '24px') === '24px' ? 'selected' : '' ?>>24px</option>
                                            <option value="26px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_mobile'] ?? '24px') === '26px' ? 'selected' : '' ?>>26px</option>
                                            <option value="28px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_mobile'] ?? '24px') === '28px' ? 'selected' : '' ?>>28px</option>
                                            <option value="30px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h2_mobile'] ?? '24px') === '30px' ? 'selected' : '' ?>>30px</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tamanho_h3_mobile" class="form-label">H3 (Mobile):</label>
                                        <select class="form-select" id="tamanho_h3_mobile" name="tamanho_h3_mobile">
                                            <option value="20px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_mobile'] ?? '20px') === '20px' ? 'selected' : '' ?>>20px</option>
                                            <option value="22px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_mobile'] ?? '20px') === '22px' ? 'selected' : '' ?>>22px</option>
                                            <option value="24px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_mobile'] ?? '20px') === '24px' ? 'selected' : '' ?>>24px</option>
                                            <option value="26px" <?= ($configs['fontes']['titulo_conteudo']['tamanho_h3_mobile'] ?? '20px') === '26px' ? 'selected' : '' ?>>26px</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="preview" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            As alterações serão aplicadas automaticamente após salvar.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Preview do Header</h4>
                                <div class="border rounded p-3" style="background-color: <?= $configs['cores']['header']['cor_fundo'] ?? '#ffffff' ?>; color: <?= $configs['cores']['header']['cor_texto'] ?? '#333333' ?>;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="logo">Brasil Hilário</div>
                                        <nav>
                                            <a href="#" style="color: <?= $configs['cores']['header']['cor_link'] ?? '#007bff' ?>;">Início</a>
                                            <a href="#" style="color: <?= $configs['cores']['header']['cor_link'] ?? '#007bff' ?>;">Sobre</a>
                                            <a href="#" style="color: <?= $configs['cores']['header']['cor_link'] ?? '#007bff' ?>;">Contato</a>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Preview do Conteúdo</h4>
                                <div class="border rounded p-3">
                                    <h1 style="font-family: <?= $configs['fontes']['titulo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>; font-size: <?= $configs['fontes']['titulo']['tamanho'] ?? '28px' ?>; color: <?= $configs['cores']['site']['cor_primaria'] ?? '#007bff' ?>;">Título Principal</h1>
                                    <h2 style="font-family: <?= $configs['fontes']['titulo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>; font-size: <?= $configs['fontes']['subtitulo']['tamanho'] ?? '20px' ?>; color: <?= $configs['cores']['site']['cor_secundaria'] ?? '#6c757d' ?>;">Subtítulo</h2>
                                    <p style="font-family: <?= $configs['fontes']['paragrafo']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>; font-size: <?= $configs['fontes']['paragrafo']['tamanho'] ?? '16px' ?>;">Este é um exemplo de parágrafo com as configurações de fonte aplicadas.</p>
                                    
                                    <div class="card mb-3" style="font-family: <?= $configs['fontes']['card']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>;">
                                        <div class="card-body">
                                            <h5 class="card-title">Título do Card</h5>
                                            <p class="card-text">Texto do card com fonte personalizada.</p>
                                            <small class="text-muted" style="font-family: <?= $configs['fontes']['meta']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>;">Meta texto - 15/01/2024</small>
                                        </div>
                                    </div>
                                    
                                    <div class="sidebar-preview" style="font-family: <?= $configs['fontes']['sidebar']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>;">
                                        <h6>Sidebar Preview</h6>
                                        <a href="#" class="d-block">Link da sidebar</a>
                                    </div>
                                    
                                    <button class="btn btn-primary" style="font-family: <?= $configs['fontes']['botao']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>; background-color: <?= $configs['cores']['site']['cor_primaria'] ?? '#007bff' ?>;">Botão Primário</button>
                                    <button class="btn btn-success" style="font-family: <?= $configs['fontes']['botao']['fonte'] ?? 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' ?>; background-color: <?= $configs['cores']['site']['cor_sucesso'] ?? '#28a745' ?>;">Botão Sucesso</button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                    <a href="configuracoes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>


<script>
document.querySelectorAll('input[type="color"], select').forEach(input => {
    input.addEventListener('change', function() {
        console.log('Configuração alterada:', this.name, this.value);
    });
});

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            submitBtn.disabled = true;
            
            submitBtn.click();
            
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }
        
        showNotification('Salvando configurações...', 'info');
    }
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}


document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.title = 'Pressione CTRL+S para salvar rapidamente';
    }
    
    setTimeout(() => {
        showNotification('💡 Dica: Use CTRL+S para salvar rapidamente!', 'info');
    }, 1000);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const personalizarCheckbox = document.getElementById('personalizar_fontes');
    const fontesPersonalizadas = document.getElementById('fontes_personalizadas');
    const fonteGeral = document.getElementById('fonte_geral');
    
    function toggleFontesPersonalizadas() {
        if (personalizarCheckbox.checked) {
            fontesPersonalizadas.style.display = 'block';
            fonteGeral.disabled = true;
        } else {
            fontesPersonalizadas.style.display = 'none';
            fonteGeral.disabled = false;
        }
    }
    

    toggleFontesPersonalizadas();
    
    personalizarCheckbox.addEventListener('change', toggleFontesPersonalizadas);
    
    const previewElements = document.querySelectorAll('[data-preview]');
    previewElements.forEach(element => {
        element.addEventListener('change', function() {
            const previewTarget = this.getAttribute('data-preview');
            const previewElement = document.querySelector(previewTarget);
            if (previewElement) {
                previewElement.style.fontFamily = this.value;
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 
