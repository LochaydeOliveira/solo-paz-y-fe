<?php
// Iniciar buffer de saída
ob_start();

require_once 'includes/db.php';
require_once 'config/config.php';

// Incluir o header
include 'includes/header.php';
?>

<div class="row">
    <!-- Conteúdo Principal -->
    <div class="col-lg-8">
        <article class="blog-post" data-aos="fade-up">
            <h1 class="display-5 fw-bold mb-3 header-pg-ctt">Entre em Contato</h1>
            
            <div class="post-content width-custom">
                <p>Preencha o formulário abaixo para entrar em contato conosco. Responderemos o mais breve possível.</p>

                <form action="<?php echo BLOG_PATH; ?>/api/enviar-contato.php" method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="assunto" class="form-label">Assunto</label>
                        <input type="text" class="form-control" id="assunto" name="assunto" required>
                    </div>

                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem</label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                </form>
            </div>
        </article>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <?php include 'includes/sidebar.php'; ?>
    </div>
</div>

<?php 
include 'includes/footer.php';
// Enviar o buffer de saída
ob_end_flush();
?>
