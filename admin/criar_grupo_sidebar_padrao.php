<?php
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

// Garantir login
check_login();

header('Content-Type: text/html; charset=utf-8');

function out($msg){ echo '<div style="font-family:Inter,system-ui,Segoe UI,Arial;font-size:14px;margin:6px 0;">' . htmlspecialchars($msg) . '</div>'; }

try {
    // Verificar se tabelas de grupos existem
    $tabelas = ['grupos_anuncios','grupos_anuncios_items','grupos_anuncios_posts'];
    foreach ($tabelas as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE '" . $t . "'");
        if (!$stmt->fetch()) {
            out("ERRO: Tabela '".$t."' não existe. Configure o sistema de grupos antes.");
            exit;
        }
    }

    // 1) Criar/obter grupo Sidebar Padrão
    $stmt = $pdo->prepare("SELECT id FROM grupos_anuncios WHERE localizacao='sidebar' AND nome = 'Sidebar Padrão' LIMIT 1");
    $stmt->execute();
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($grupo) {
        $grupoId = (int)$grupo['id'];
        out("Grupo 'Sidebar Padrão' já existe (id=$grupoId).");
    } else {
        $stmt = $pdo->prepare("INSERT INTO grupos_anuncios (nome, localizacao, layout, marca, posts_especificos, aparecer_inicio, ativo, criado_em) VALUES ('Sidebar Padrão', 'sidebar', 'grade', '', 1, 0, 1, NOW())");
        $stmt->execute();
        $grupoId = (int)$pdo->lastInsertId();
        out("Grupo 'Sidebar Padrão' criado (id=$grupoId).");
    }

    // 2) Associar anúncios com localizacao='sidebar' e ativos
    $stmt = $pdo->prepare("SELECT id FROM anuncios WHERE localizacao='sidebar' AND ativo=1");
    $stmt->execute();
    $anuncios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($anuncios)) {
        out("Aviso: Não há anúncios ativos configurados para 'sidebar'.");
    } else {
        $ordem = 0;
        $stmtIns = $pdo->prepare("INSERT IGNORE INTO grupos_anuncios_items (grupo_id, anuncio_id, ordem) VALUES (?, ?, ?)");
        foreach ($anuncios as $anuncioId) {
            $stmtIns->execute([$grupoId, (int)$anuncioId, $ordem++]);
        }
        out("Anúncios sidebar ativos associados ao grupo: " . count($anuncios));
    }

    // 3) Associar alguns posts para teste
    // Estratégia: se não houver posts associados ainda, associar os 5 posts publicados mais recentes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos_anuncios_posts WHERE grupo_id = ?");
    $stmt->execute([$grupoId]);
    $jaTemPosts = (int)$stmt->fetchColumn();

    if ($jaTemPosts === 0) {
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE publicado=1 ORDER BY data_publicacao DESC LIMIT 5");
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($posts)) {
            $stmtInsP = $pdo->prepare("INSERT IGNORE INTO grupos_anuncios_posts (grupo_id, post_id) VALUES (?, ?)");
            foreach ($posts as $postId) {
                $stmtInsP->execute([$grupoId, (int)$postId]);
            }
            out("Posts de teste associados ao grupo: " . count($posts) . " (mais recentes)");
        } else {
            out("Aviso: Não encontrei posts publicados para associar.");
        }
    } else {
        out("Grupo já possuía posts associados. Mantidos sem alteração.");
    }

    out("Concluído. Acesse um post publicado para verificar a sidebar. Para logs, use ?debug_sidebar=1.");
    echo '<div style="margin-top:12px;"><a href="grupos-anuncios.php" class="btn btn-primary">Voltar</a></div>';
} catch (Exception $e) {
    out('ERRO: ' . $e->getMessage());
}
?>


