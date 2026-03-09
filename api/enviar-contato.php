<?php
require_once '../includes/db.php';
require_once '../config/config.php';

// Iniciar buffer de saída
ob_start();

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BLOG_PATH . '/contato');
    exit;
}

// Validar e sanitizar os dados
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$assunto = filter_input(INPUT_POST, 'assunto', FILTER_SANITIZE_STRING);
$mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);

// Validar campos obrigatórios
if (!$nome || !$email || !$assunto || !$mensagem) {
    $_SESSION['erro'] = 'Por favor, preencha todos os campos.';
    header('Location: ' . BLOG_PATH . '/contato');
    exit;
}

// Validar e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro'] = 'Por favor, forneça um e-mail válido.';
    header('Location: ' . BLOG_PATH . '/contato');
    exit;
}

try {
    // Inserir mensagem no banco de dados
    $stmt = $pdo->prepare("
        INSERT INTO mensagens (nome, email, assunto, mensagem, data_envio) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$nome, $email, $assunto, $mensagem]);

    // Enviar e-mail de notificação
    $to = ADMIN_EMAIL;
    $subject = "Nova mensagem de contato: " . $assunto;
    $message = "Nome: $nome\n";
    $message .= "E-mail: $email\n";
    $message .= "Assunto: $assunto\n\n";
    $message .= "Mensagem:\n$mensagem";
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    mail($to, $subject, $message, $headers);

    $_SESSION['sucesso'] = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
} catch (PDOException $e) {
    $_SESSION['erro'] = 'Erro ao enviar mensagem. Por favor, tente novamente mais tarde.';
    error_log("Erro ao enviar mensagem: " . $e->getMessage());
}

header('Location: ' . BLOG_PATH . '/contato');
exit; 