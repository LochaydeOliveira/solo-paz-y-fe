<?php
require_once '../config/config.php';
require_once '../includes/db.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método não permitido']));
}

// Verificar se um arquivo foi enviado
if (!isset($_FILES['file'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Nenhum arquivo enviado']));
}

$file = $_FILES['file'];

// Verificar erros no upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die(json_encode(['error' => 'Erro no upload do arquivo']));
}

// Verificar o tipo do arquivo
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    die(json_encode(['error' => 'Tipo de arquivo não permitido']));
}

// Verificar o tamanho do arquivo
if ($file['size'] > UPLOAD_MAX_SIZE) {
    http_response_code(400);
    die(json_encode(['error' => 'Arquivo muito grande']));
}

// Criar diretório de uploads se não existir
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Gerar nome único para o arquivo
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = UPLOAD_PATH . '/' . $filename;

// Mover o arquivo
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    die(json_encode(['error' => 'Erro ao salvar o arquivo']));
}

// Retornar URL do arquivo
$file_url = BLOG_URL . '/uploads/' . $filename;
echo json_encode(['location' => $file_url]);
?> 