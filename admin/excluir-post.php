<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php'; // deve conter $pdo
require_once 'includes/auth.php';

// Verificar se o ID foi fornecido
$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (empty($post_id)) {
    $_SESSION['error_message'] = "ID do post não fornecido.";
    header('Location: ' . ADMIN_URL . '/posts.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Excluir registros relacionados na tabela post_tags
    $stmt = $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?");
    $stmt->execute([$post_id]);

    // Excluir o post da tabela posts
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);

    $pdo->commit();

    $_SESSION['success_message'] = "Post excluído com sucesso!";
    header('Location: ' . ADMIN_URL . '/posts.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Erro ao excluir o post: " . $e->getMessage();
    header('Location: ' . ADMIN_URL . '/posts.php');
    exit;
}
?>
