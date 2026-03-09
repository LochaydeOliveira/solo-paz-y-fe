<?php
require_once __DIR__ . '/db.php'; // Deve definir $pdo

class ConfigManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function get($chave, $padrao = null) {
        $stmt = $this->pdo->prepare("SELECT valor, tipo FROM configuracoes WHERE chave = :chave LIMIT 1");
        $stmt->execute([':chave' => $chave]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->convertValue($row['valor'], $row['tipo']);
        }
        
        return $padrao;
    }
    
    public function set($chave, $valor, $tipo = 'string', $grupo = 'geral') {
        // Verificar se a configuração já existe
        if ($this->exists($chave)) {
            $stmt = $this->pdo->prepare("
                UPDATE configuracoes 
                SET valor = :valor, tipo = :tipo, grupo = :grupo, atualizado_em = NOW() 
                WHERE chave = :chave
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO configuracoes (chave, valor, tipo, grupo, atualizado_em) 
                VALUES (:chave, :valor, :tipo, :grupo, NOW())
            ");
        }
        
        return $stmt->execute([
            ':chave' => $chave,
            ':valor' => $valor,
            ':tipo' => $tipo,
            ':grupo' => $grupo
        ]);
    }
    
    public function getGroup($grupo) {
        $stmt = $this->pdo->prepare("SELECT chave, valor, tipo FROM configuracoes WHERE grupo = :grupo ORDER BY chave");
        $stmt->execute([':grupo' => $grupo]);
        
        $configs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $configs[$row['chave']] = [
                'valor' => $this->convertValue($row['valor'], $row['tipo']),
                'tipo' => $row['tipo']
            ];
        }
        return $configs;
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT chave, valor, tipo, grupo FROM configuracoes ORDER BY grupo, chave");
        
        $configs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $configs[$row['chave']] = [
                'valor' => $this->convertValue($row['valor'], $row['tipo']),
                'tipo' => $row['tipo'],
                'grupo' => $row['grupo']
            ];
        }
        return $configs;
    }
    
    public function delete($chave) {
        $stmt = $this->pdo->prepare("DELETE FROM configuracoes WHERE chave = :chave");
        return $stmt->execute([':chave' => $chave]);
    }
    
    public function exists($chave) {
        $stmt = $this->pdo->prepare("SELECT id FROM configuracoes WHERE chave = :chave LIMIT 1");
        $stmt->execute([':chave' => $chave]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    private function convertValue($valor, $tipo) {
        switch ($tipo) {
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $valor;
            case 'float':
                return (float) $valor;
            case 'array':
            case 'json':
                return json_decode($valor, true) ?: [];
            default:
                return $valor;
        }
    }
}
