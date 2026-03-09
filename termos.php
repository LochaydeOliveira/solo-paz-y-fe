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
            <h1 class="display-5 fw-bold mb-3">Termos de Uso</h1>
            
            <div class="post-content">
                <p>Última atualização: <?php echo date('d/m/Y'); ?></p>

                <h2>1. Aceitação dos Termos</h2>
                <p>Ao acessar e usar este blog, você concorda em cumprir e ficar vinculado aos seguintes termos e condições de uso.</p>

                <h2>2. Uso do Conteúdo</h2>
                <p>Todo o conteúdo deste blog é protegido por direitos autorais. Você pode:</p>
                <ul>
                    <li>Ler e compartilhar o conteúdo para uso pessoal</li>
                    <li>Citar trechos do conteúdo, desde que cite a fonte</li>
                    <li>Comentar nos posts, respeitando as regras de conduta</li>
                </ul>
                <p>Você não pode:</p>
                <ul>
                    <li>Copiar e republicar o conteúdo sem autorização</li>
                    <li>Usar o conteúdo para fins comerciais sem permissão</li>
                    <li>Modificar ou criar trabalhos derivados do conteúdo</li>
                </ul>

                <h2>3. Comentários e Interações</h2>
                <p>Ao comentar em nossos posts, você concorda em:</p>
                <ul>
                    <li>Fornecer informações verdadeiras</li>
                    <li>Respeitar outros usuários</li>
                    <li>Não publicar conteúdo ofensivo ou ilegal</li>
                    <li>Não fazer spam ou propaganda não autorizada</li>
                </ul>

                <h2>4. Responsabilidade</h2>
                <p>O conteúdo deste blog é fornecido "como está", sem garantias de qualquer tipo. Não nos responsabilizamos por:</p>
                <ul>
                    <li>Precisão ou atualidade das informações</li>
                    <li>Danos causados pelo uso do conteúdo</li>
                    <li>Conteúdo de sites externos linkados</li>
                </ul>

                <h2>5. Modificações</h2>
                <p>Reservamo-nos o direito de:</p>
                <ul>
                    <li>Modificar estes termos a qualquer momento</li>
                    <li>Remover ou editar comentários inadequados</li>
                    <li>Bloquear usuários que violem os termos</li>
                </ul>

                <h2>6. Privacidade</h2>
                <p>O uso de suas informações pessoais é regido por nossa <a href="<?php echo BLOG_PATH; ?>/privacidade">Política de Privacidade</a>.</p>

                <h2>7. Lei Aplicável</h2>
                <p>Estes termos são regidos pelas leis do Brasil. Qualquer disputa será submetida à jurisdição exclusiva dos tribunais brasileiros.</p>

                <h2>8. Contato</h2>
                <p>Se você tiver dúvidas sobre estes termos, entre em <a href="<?php echo BLOG_PATH; ?>/contato">contato conosco</a>.</p>
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