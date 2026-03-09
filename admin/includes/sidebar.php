<?php
// Verificar se é administrador
$is_admin = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
?>

<ul class="nav flex-column" style="max-width: max-content;">
    <li class="nav-item">
        <a class="nav-link" href="index.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="posts.php">
            <i class="fas fa-newspaper"></i> Posts
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="categorias.php">
            <i class="fas fa-tags"></i> Categorias
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="anuncios.php">
            <i class="fas fa-ad"></i> Anúncios
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="grupos-anuncios.php">
            <i class="fas fa-layer-group"></i> Grupos de Anúncios
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="usuarios.php">
            <i class="fas fa-users"></i> Usuários
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="configuracoes.php">
            <i class="fas fa-cog"></i> Configurações
        </a>
    </li>
    
    <?php if ($is_admin): ?>
    <li class="nav-item">
        <a class="nav-link" href="configuracoes-visuais.php">
            <i class="fas fa-palette"></i> Configurações Visuais
        </a>
    </li>
    <?php endif; ?>
    
    <li class="nav-item">
        <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </li>
</ul> 