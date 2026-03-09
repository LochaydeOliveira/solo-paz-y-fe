<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configurações
require_once __DIR__ . '/../../config/config.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Painel Administrativo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css-custom/style-custom-adm.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">
    <link rel="apple-touch-icon" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">
    <link rel="shortcut icon" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">

    <style>
        /* Ajustes mínimos para melhorar a aparência */
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .navbar {
            background-color: #fff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-brand img {
            max-height: 35px;
            width: auto;
        }
        
        /* Correção da cor do texto do sidebar */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .sidebar .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff !important;
            background-color: #0d6efd;
        }
        
        .sidebar .nav-link i {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .sidebar .nav-link:hover i {
            color: #fff !important;
        }
        
        .sidebar .nav-link.active i {
            color: #fff !important;
        }
        
        /* Correção do alinhamento do conteúdo */
        .main-content {
            padding: 0;
        }
        
        .col-md-9.ms-sm-auto.col-lg-10.px-md-4 {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
            padding-bottom: 4rem !important;
            padding-top: 3rem !important;
        }
        
        /* Garantir que o conteúdo preencha toda a largura */
        .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }
        
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        /* Ajuste específico para o conteúdo principal */
        .col-md-9.ms-sm-auto.col-lg-10.px-md-4 .container-fluid {
            max-width: 100%;
            width: 100%;
        }
        
        /* Garantir que o conteúdo principal ocupe toda a largura */
        main {
            width: 100%;
            max-width: 100%;
        }
        
        /* Remover margens desnecessárias */
        .container-fluid .row {
            margin: 0;
        }
        
        /* Garantir que o conteúdo preencha toda a largura disponível */
        .col-md-9.ms-sm-auto.col-lg-10.px-md-4 > * {
            width: 100%;
            max-width: 100%;
        }
        
        /* Garantir altura automática e preenchimento da tela */
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        .container-fluid {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .row {
            flex: 1;
            display: flex;
        }
        
        .sidebar {
            flex-shrink: 0;
        }
        
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Garantir que o conteúdo principal ocupe todo o espaço disponível */
        .col-md-9.ms-sm-auto.col-lg-10.px-md-4 {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 60px); /* 60px é a altura aproximada do header */
        }
        
        /* Garantir que o conteúdo interno se expanda */
        .col-md-9.ms-sm-auto.col-lg-10.px-md-4 > * {
            flex: 0 0 auto; /* Não expandir automaticamente */
        }
        
        /* Ajustar elementos internos para comportamento normal */
        .card, .table-responsive, form, .alert, .nav-tabs, .tab-content {
            flex: none;
            display: block;
        }
        
        /* Garantir que containers de conteúdo funcionem normalmente */
        .row .col-md-12, .row .col-md-6, .row .col-md-4, .row .col-md-3 {
            flex: none;
            display: block;
        }
        
        /* Manter elementos de layout específicos */
        .d-flex {
            display: flex !important;
        }
        
        .flex-column {
            flex-direction: column !important;
        }
        
        .flex-1 {
            flex: 1 !important;
        }
        
        /* Melhorias para cards */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Melhorias para tabelas */
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        /* Melhorias para botões */
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
        }
        
        /* Melhorias para badges */
        .badge {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Melhorias para alertas */
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        
        /* Melhorias para formulários */
        .form-control, .form-select {
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        /* Melhorias para modais */
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 0.5rem 0.5rem;
        }
    </style>
</head>
<body>
    <header class="navbar sticky-top flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="index.php">
            <img width="150" src="../assets/img/logo-brasil-hilario-quadrada-svg.svg" alt="Brasil Hilário" class="img-fluid">
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <?php include 'sidebar.php'; ?>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 sz-fnt">