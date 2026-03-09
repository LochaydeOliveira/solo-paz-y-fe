<?php
require_once __DIR__ . '/db.php';

class SectionManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Gerenciar seções
    public function getSecao($slug) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM secoes_site WHERE slug = ? AND ativo = 1
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllSecoes() {
        $stmt = $this->pdo->query("
            SELECT * FROM secoes_site WHERE ativo = 1 ORDER BY posicao
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createSecao($nome, $slug, $tipo = 'custom', $configuracoes = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO secoes_site (nome, slug, tipo, configuracoes) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$nome, $slug, $tipo, $configuracoes]);
    }
    
    public function updateSecao($id, $dados) {
        $stmt = $this->pdo->prepare("
            UPDATE secoes_site 
            SET nome = ?, slug = ?, tipo = ?, posicao = ?, configuracoes = ?, ativo = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['slug'],
            $dados['tipo'],
            $dados['posicao'],
            $dados['configuracoes'],
            $dados['ativo'],
            $id
        ]);
    }
    
    public function deleteSecao($id) {
        $stmt = $this->pdo->prepare("DELETE FROM secoes_site WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Gerenciar elementos
    public function getElementosSecao($secao_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM elementos_secao 
            WHERE secao_id = ? AND ativo = 1 
            ORDER BY posicao
        ");
        $stmt->execute([$secao_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createElemento($secao_id, $tipo, $nome, $conteudo = '', $configuracoes = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO elementos_secao (secao_id, tipo, nome, conteudo, configuracoes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$secao_id, $tipo, $nome, $conteudo, $configuracoes]);
    }
    
    public function updateElemento($id, $dados) {
        $stmt = $this->pdo->prepare("
            UPDATE elementos_secao 
            SET nome = ?, conteudo = ?, configuracoes = ?, posicao = ?, ativo = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['conteudo'],
            $dados['configuracoes'],
            $dados['posicao'],
            $dados['ativo'],
            $id
        ]);
    }
    
    public function deleteElemento($id) {
        $stmt = $this->pdo->prepare("DELETE FROM elementos_secao WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Gerenciar itens de menu
    public function getItensMenu($elemento_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM itens_menu 
            WHERE elemento_id = ? AND ativo = 1 
            ORDER BY posicao
        ");
        $stmt->execute([$elemento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createItemMenu($elemento_id, $texto, $url, $tipo = 'interno', $alvo = '_self', $icone = '') {
        $stmt = $this->pdo->prepare("
            INSERT INTO itens_menu (elemento_id, texto, url, tipo, alvo, icone) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$elemento_id, $texto, $url, $tipo, $alvo, $icone]);
    }
    
    public function updateItemMenu($id, $dados) {
        $stmt = $this->pdo->prepare("
            UPDATE itens_menu 
            SET texto = ?, url = ?, tipo = ?, alvo = ?, icone = ?, posicao = ?, ativo = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['texto'],
            $dados['url'],
            $dados['tipo'],
            $dados['alvo'],
            $dados['icone'],
            $dados['posicao'],
            $dados['ativo'],
            $id
        ]);
    }
    
    public function deleteItemMenu($id) {
        $stmt = $this->pdo->prepare("DELETE FROM itens_menu WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Renderizar seção
    public function renderSecao($slug) {
        $secao = $this->getSecao($slug);
        if (!$secao) return '';
        
        $elementos = $this->getElementosSecao($secao['id']);
        $html = "<div class='secao-{$secao['slug']}'>";
        
        foreach ($elementos as $elemento) {
            $html .= $this->renderElemento($elemento);
        }
        
        $html .= "</div>";
        return $html;
    }
    
    private function renderElemento($elemento) {
        switch ($elemento['tipo']) {
            case 'menu':
                return $this->renderMenu($elemento);
            case 'logo':
                return $this->renderLogo($elemento);
            case 'texto':
                return $this->renderTexto($elemento);
            case 'imagem':
                return $this->renderImagem($elemento);
            case 'link':
                return $this->renderLink($elemento);
            default:
                return "<div class='elemento-{$elemento['tipo']}'>{$elemento['conteudo']}</div>";
        }
    }
    
    private function renderMenu($elemento) {
        $itens = $this->getItensMenu($elemento['id']);
        $html = "<nav class='menu-{$elemento['nome']}'>";
        $html .= "<ul class='nav'>";
        
        foreach ($itens as $item) {
            $target = $item['alvo'] ? " target='{$item['alvo']}'" : '';
            $icone = $item['icone'] ? "<i class='{$item['icone']}'></i> " : '';
            $html .= "<li class='nav-item'>";
            $html .= "<a class='nav-link' href='{$item['url']}'{$target}>{$icone}{$item['texto']}</a>";
            $html .= "</li>";
        }
        
        $html .= "</ul></nav>";
        return $html;
    }
    
    private function renderLogo($elemento) {
        return "<div class='logo'>{$elemento['conteudo']}</div>";
    }
    
    private function renderTexto($elemento) {
        return "<div class='texto'>{$elemento['conteudo']}</div>";
    }
    
    private function renderImagem($elemento) {
        $config = json_decode($elemento['configuracoes'], true);
        $alt = $config['alt'] ?? '';
        $class = $config['class'] ?? '';
        return "<img src='{$elemento['conteudo']}' alt='{$alt}' class='{$class}'>";
    }
    
    private function renderLink($elemento) {
        $config = json_decode($elemento['configuracoes'], true);
        $target = $config['target'] ?? '_self';
        $class = $config['class'] ?? '';
        return "<a href='{$elemento['conteudo']}' target='{$target}' class='{$class}'>{$elemento['nome']}</a>";
    }
} 