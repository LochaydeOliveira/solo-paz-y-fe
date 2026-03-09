<?php
$senha = '154719';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Senha original: " . $senha . "\n";
echo "Hash gerado: " . $hash . "\n";

// Testar a verificação
$teste = password_verify($senha, $hash);
echo "Verificação: " . ($teste ? "OK" : "Falhou") . "\n";
?> 
