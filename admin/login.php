<?php
// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Função para log
function log_error($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, __DIR__ . '/error.log');
}

try {
    session_start();
    log_error("Iniciando processo de login");

    require_once '../config/config.php';
    log_error("Config carregada");

    require_once '../includes/db.php'; // aqui deve conter a variável $pdo
    log_error("DB carregada");

    require_once 'includes/auth.php';
    log_error("Auth carregada");

    if (isset($_SESSION['usuario_id'])) {
        log_error("Usuário já logado, redirecionando");
        header('Location: index.php');
        exit;
    }

    $erro = '';
    $msg = '';

    if (isset($_GET['msg']) && $_GET['msg'] === 'timeout') {
        $msg = 'Sua sessão expirou. Por favor, faça login novamente.';
        log_error("Mensagem de timeout detectada");
    }

    function clean_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        log_error("Processando formulário POST");

        $email = clean_input($_POST['email']);
        $senha = $_POST['senha'];

        log_error("Tentativa de login para email: $email");

        if (empty($email) || empty($senha)) {
            $erro = 'Por favor, preencha todos os campos.';
            log_error("Campos vazios detectados");
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND status = 'ativo' LIMIT 1");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                log_error("Usuário encontrado: " . ($usuario ? "Sim" : "Não"));

                if ($usuario) {
                    log_error("Verificando senha...");
                    $senha_verificada = password_verify($senha, $usuario['senha']);
                    log_error("Senha verificada: " . ($senha_verificada ? "Correta" : "Incorreta"));

                    if ($senha_verificada) {
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nome'] = $usuario['nome'];
                        $_SESSION['usuario_email'] = $usuario['email'];
                        $_SESSION['usuario_tipo'] = $usuario['tipo'];
                        $_SESSION['ultimo_acesso'] = time();

                        $updateStmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id");
                        $updateStmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
                        $updateStmt->execute();

                        log_error("Login bem sucedido para: $email");
                        header('Location: index.php');
                        exit;
                    }
                }

                $erro = 'Email ou senha inválidos.';
                log_error("Tentativa de login falhou para: $email");

            } catch (Exception $e) {
                $erro = 'Erro ao tentar fazer login. Por favor, tente novamente.';
                log_error("Erro de login: " . $e->getMessage());
            }
        }
    }

} catch (Exception $e) {
    log_error("Erro crítico: " . $e->getMessage());
    $erro = "Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: none;
            border-bottom: none;
            text-align: center;
            padding: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
        }
        .btn-primary {
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <img src="../assets/img/logo-brasil-hilario-quadrada-svg.svg" alt="Logo" class="logo">
                <h4>Painel Administrativo</h4>
            </div>
            <div class="card-body">
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <?php if ($msg): ?>
                    <div class="alert alert-warning"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                    <a style="font-size: 14px;display: flex;justify-content: center;margin: 10px 0;color: #878787;" href="https://www.brasilhilario.com.br/">Voltar ao Blog</a>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
