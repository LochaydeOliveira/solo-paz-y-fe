<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Verifica se o usuário está autenticado
if (!isLoggedIn()) {
    http_response_code(401);
    die(json_encode(['error' => 'Não autorizado']));
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método não permitido']));
}

// Verifica se há um arquivo
if (!isset($_FILES['file'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Nenhum arquivo enviado']));
}

$file = $_FILES['file'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validações
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    die(json_encode(['error' => 'Tipo de arquivo não permitido']));
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    die(json_encode(['error' => 'Arquivo muito grande']));
}

// Cria o diretório de uploads se não existir
$upload_dir = '../uploads/' . date('Y/m');
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Gera um nome único para o arquivo
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = $upload_dir . '/' . $filename;

// Move o arquivo
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Retorna a URL do arquivo
    $file_url = '/uploads/' . date('Y/m') . '/' . $filename;
    echo json_encode(['location' => $file_url]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar o arquivo']);
} 
