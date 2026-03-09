<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminhos corrigidos para carregar as dependências
require_once '../includes/db.php';
require_once '../config/admin_ips.php';

// 1. IDENTIFICAÇÃO DE ADMIN (O seu escudo)
$meu_ip_atual = '179.48.3.74'; 
$is_admin = (
    $_SERVER['REMOTE_ADDR'] === $meu_ip_atual || 
    isset($_SESSION['user_id']) || 
    isset($_COOKIE['admin_ignore'])
);

// Se for Admin, respondemos sucesso mas não alteramos o banco
if ($is_admin) {
    echo json_encode(['success' => true, 'counted' => false, 'reason' => 'admin_blocked']);
    exit;
}

// 2. VALIDAÇÃO DA REQUISIÇÃO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? null;

if (!$postId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID ausente']);
    exit;
}

// 3. INCREMENTO NO BANCO
try {
    // A função shouldCountView agora será encontrada pois carregamos o admin_ips.php acima
    if (shouldCountView($postId)) {
        $stmt = $pdo->prepare("UPDATE posts SET visualizacoes = visualizacoes + 1 WHERE id = ?");
        $stmt->execute([$postId]); // Corrigido de $stmt.execute para $stmt->execute
        
        // Criar o cookie de visualização única para este post
        setcookie('viewed_post_id_' . $postId, 'true', time() + 1800, '/');
        
        echo json_encode(['success' => true, 'counted' => true]);
    } else {
        echo json_encode(['success' => true, 'counted' => false, 'reason' => 'flood_or_bot']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}