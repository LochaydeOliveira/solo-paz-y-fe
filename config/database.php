<?php

define('DB_HOST_LOCAL', 'localhost');
define('DB_HOST_IP', '192.185.222.27');
define('DB_NAME', 'paymen58_solo_paz_y_fe');
define('DB_USER', 'paymen58');
define('DB_PASS', 'u4q7+B6ly)obP_gxN9sNe');



$dsnLocal = "mysql:host=" . DB_HOST_LOCAL . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$dsnIP = "mysql:host=" . DB_HOST_IP . ";dbname=" . DB_NAME . ";charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {

    $pdo = new PDO($dsnLocal, DB_USER, DB_PASS, $options);
    
    $pdo->query('SELECT 1');
} catch (PDOException $eLocal) {
    try {
        $pdo = new PDO($dsnIP, DB_USER, DB_PASS, $options);
        $pdo->query('SELECT 1');
    } catch (PDOException $eIP) {
        error_log("Erro na conexão com o banco de dados (localhost): " . $eLocal->getMessage());
        error_log("Erro na conexão com o banco de dados (IP): " . $eIP->getMessage());
        die("Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
    }
}
