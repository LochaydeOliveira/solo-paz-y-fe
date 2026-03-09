<?php
// Define o cookie PHP por segurança (5 anos)
setcookie('admin_ignore', 'true', time() + (5 * 365 * 24 * 60 * 60), '/');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configurando Admin</title>
</head>
<body>
    <h1>Marcando seu dispositivo...</h1>
    <script>
        // Grava no LocalStorage (isso é quase impossível de sair sozinho)
        localStorage.setItem('admin_ignore', 'true');
        
        // Também tenta reforçar o cookie via JS
        document.cookie = "admin_ignore=true; max-age=" + (5 * 365 * 24 * 60 * 60) + "; path=/";
        
        alert('Este perfil/navegador agora é invisível para as métricas!');
        window.location.href = 'index.php';
    </script>
</body>
</html>