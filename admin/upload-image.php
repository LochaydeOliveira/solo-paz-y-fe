<?php
require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Você precisa estar logado para fazer upload de imagens.']);
    exit;
}

// Verifica se o usuário é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['error'] = 'Você não tem permissão para acessar esta página.';
    header('Location: index.php');
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

// Verifica se um arquivo foi enviado
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo foi enviado ou ocorreu um erro no upload.']);
    exit;
}

$file = $_FILES['file'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Verifica o tipo do arquivo
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de arquivo não permitido. Apenas imagens JPG, PNG, GIF e WebP são aceitas.']);
    exit;
}

// Verifica o tamanho do arquivo
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'O arquivo é muito grande. O tamanho máximo permitido é 5MB.']);
    exit;
}

// Cria o diretório de uploads se não existir
$upload_dir = '../uploads/images/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Gera um nome único para o arquivo
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move o arquivo para o diretório de uploads
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Retorna a URL da imagem
    $image_url = BLOG_URL . '/uploads/images/' . $filename;
    echo json_encode(['location' => $image_url]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar o arquivo.']);
    exit;
}
?>
