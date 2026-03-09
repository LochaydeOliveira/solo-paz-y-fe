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
            <h1 class="display-5 fw-bold mb-3">Política de Privacidade</h1>
            
            <div class="post-content">
                <p>Última atualização: <?php echo date('d/m/Y'); ?></p>

                <h2>1. Informações que Coletamos</h2>
                <p>Coletamos informações que você nos fornece diretamente, incluindo:</p>
                <ul>
                    <li>Nome e endereço de e-mail quando você se inscreve em nossa newsletter</li>
                    <li>Informações de contato quando você preenche nosso formulário de contato</li>
                    <li>Comentários e interações que você faz em nossos posts</li>
                </ul>

                <h2>2. Como Usamos suas Informações</h2>
                <p>Utilizamos as informações coletadas para:</p>
                <ul>
                    <li>Fornecer, manter e melhorar nossos serviços</li>
                    <li>Enviar newsletters e atualizações (com seu consentimento)</li>
                    <li>Responder a suas perguntas e solicitações</li>
                    <li>Personalizar sua experiência no blog</li>
                </ul>

                <h2>3. Cookies e Tecnologias Similares</h2>
                <p>Utilizamos cookies e tecnologias similares para:</p>
                <ul>
                    <li>Lembrar suas preferências</li>
                    <li>Entender como você usa nosso site</li>
                    <li>Melhorar sua experiência de navegação</li>
                </ul>

                <h2>4. Compartilhamento de Informações</h2>
                <p>Não vendemos suas informações pessoais. Podemos compartilhar informações com:</p>
                <ul>
                    <li>Prestadores de serviços que nos ajudam a operar o site</li>
                    <li>Parceiros de publicidade (como Google AdSense)</li>
                    <li>Quando exigido por lei</li>
                </ul>

                <h2>5. Seus Direitos</h2>
                <p>Você tem o direito de:</p>
                <ul>
                    <li>Acessar suas informações pessoais</li>
                    <li>Corrigir informações imprecisas</li>
                    <li>Solicitar a exclusão de suas informações</li>
                    <li>Retirar seu consentimento a qualquer momento</li>
                </ul>

                <h2>6. Segurança</h2>
                <p>Implementamos medidas de segurança para proteger suas informações pessoais contra acesso não autorizado, alteração, divulgação ou destruição.</p>

                <h2>7. Alterações nesta Política</h2>
                <p>Podemos atualizar esta política periodicamente. A versão mais recente estará sempre disponível nesta página.</p>

                <h2>8. Contato</h2>
                <p>Se você tiver dúvidas sobre esta política, entre em <a href="<?php echo BLOG_PATH; ?>/contato">contato conosco</a>.</p>
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