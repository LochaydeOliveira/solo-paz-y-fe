<?php

$ADMIN_IPS = [
    '179.48.3.74', 
    '127.0.0.1',   
    '::1'
];

function isAdminIP($ip) {
    global $ADMIN_IPS;
    return in_array($ip, $ADMIN_IPS);
}

function isBot($user_agent) {
    $bots = ['bot', 'crawler', 'spider', 'googlebot', 'bingbot', 'slurp', 'duckduckbot'];
    foreach ($bots as $bot) {
        if (stripos($user_agent, $bot) !== false) return true;
    }
    return false;
}

function shouldCountView($post_id = null) {
    // 1. Bloqueio por Cookie de Admin (O que você ativou)
    if (isset($_COOKIE['admin_ignore'])) {
        return false;
    }

    // 2. Bloqueio por Sessão de Login
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (isset($_SESSION['user_id'])) {
        return false;
    }

    // 3. Bloqueio de Bots
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (isBot($user_agent)) {
        return false;
    }

    // 4. Bloqueio de IPs conhecidos
    if (isAdminIP($_SERVER['REMOTE_ADDR'])) {
        return false;
    }

    // 5. Bloqueio de visualização repetida (USANDO O ID DO POST)
    // Se não tiver ID (por segurança), não contamos.
    if (!$post_id) {
        return false;
    }
    
    $cookie_name = 'viewed_post_id_' . $post_id;
    if (isset($_COOKIE[$cookie_name])) {
        return false; // Já viu este post nos últimos 30 min
    }

    return true;
}

// Função auxiliar que você já usa no post.php
function setViewCookie($slug) {
    setcookie('viewed_post_' . $slug, 'true', time() + 3600, '/');
}