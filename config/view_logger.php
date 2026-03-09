<?php
/**
 * Sistema de Log para Visualizações
 * 
 * Este arquivo registra visualizações filtradas para monitoramento
 * e debug do sistema de contagem de visualizações.
 */

// Função para registrar visualização filtrada
function logFilteredView($post_id, $reason, $user_agent = '', $ip = '') {
    $log_file = __DIR__ . '/../logs/filtered_views.log';
    $log_dir = dirname($log_file);
    
    // Criar diretório de logs se não existir
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = sprintf(
        "[%s] Post ID: %d | Reason: %s | IP: %s | User-Agent: %s\n",
        $timestamp,
        $post_id,
        $reason,
        $ip,
        $user_agent
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Função para registrar visualização contada
function logCountedView($post_id, $ip = '') {
    $log_file = __DIR__ . '/../logs/counted_views.log';
    $log_dir = dirname($log_file);
    
    // Criar diretório de logs se não existir
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = sprintf(
        "[%s] Post ID: %d | IP: %s\n",
        $timestamp,
        $post_id,
        $ip
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Função para obter estatísticas de visualizações filtradas
function getFilteredViewsStats($days = 7) {
    $log_file = __DIR__ . '/../logs/filtered_views.log';
    
    if (!file_exists($log_file)) {
        return [];
    }
    
    $stats = [
        'admin_logged' => 0,
        'bot_detected' => 0,
        'admin_ip' => 0,
        'ajax_request' => 0,
        'cookie_exists' => 0,
        'total_filtered' => 0
    ];
    
    $lines = file($log_file, FILE_IGNORE_NEW_LINES);
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    foreach ($lines as $line) {
        if (preg_match('/\[(.*?)\]/', $line, $matches)) {
            $log_date = $matches[1];
            if ($log_date >= $cutoff_date) {
                $stats['total_filtered']++;
                
                if (strpos($line, 'admin_logged') !== false) {
                    $stats['admin_logged']++;
                } elseif (strpos($line, 'bot_detected') !== false) {
                    $stats['bot_detected']++;
                } elseif (strpos($line, 'admin_ip') !== false) {
                    $stats['admin_ip']++;
                } elseif (strpos($line, 'ajax_request') !== false) {
                    $stats['ajax_request']++;
                } elseif (strpos($line, 'cookie_exists') !== false) {
                    $stats['cookie_exists']++;
                }
            }
        }
    }
    
    return $stats;
} 