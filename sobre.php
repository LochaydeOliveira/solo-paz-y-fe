<?php
// Iniciar buffer de saída
ob_start();

require_once 'includes/db.php';
require_once 'config/config.php';

// Incluir o header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Conteúdo Principal -->
        <div class="col-lg-8">
            <article class="blog-post" data-aos="fade-up">
                <h1 class="display-5 fw-bold mb-3">Sobre Nós</h1>
                
                <div class="post-content">
                    <p>O portal <strong>Brasil Hilário</strong> nasceu com a missão de informar, entreter e conectar pessoas com os mais diversos interesses. Nosso conteúdo é produzido com seriedade, responsabilidade e atenção à diversidade de temas que impactam o dia a dia dos leitores.</p>

                    <h2 style="font-size: 20px">O que você encontra aqui</h2>
                    <ul style="padding: 0!important">
                        <li>Notícias e atualidades;</li>
                        <li>Cobertura dos principais acontecimentos do futebol;</li>
                        <li>Tecnologia, inovação e curiosidades do mundo digital;</li>
                        <li>Dicas e receitas práticas de culinária;</li>
                        <li>Curiosidades e informações sobre o reino animal;</li>
                        <li>E muito mais conteúdo útil e atualizado diariamente!;</li>
                    </ul>

                    <h2 style="font-size: 20px">Nosso compromisso</h2>
                    <p>Prezamos por conteúdo original, acessível e de qualidade. Trabalhamos para manter o site seguro, rápido e responsivo, garantindo a melhor experiência para todos os usuários.</p>

                    <p class="mt-4">Obrigado por fazer parte da nossa comunidade! Para dúvidas ou sugestões, entre em <a href="contato.php">contato conosco</a>.</p>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php 
include 'includes/footer.php';
// Enviar o buffer de saída
ob_end_flush();
?>
