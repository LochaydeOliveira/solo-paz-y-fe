<?php

class AnunciosManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Buscar anúncios para uma localização específica
     */
    public function getAnunciosPorLocalizacao($localizacao, $postId = null) {
        $sql = "SELECT a.* FROM anuncios a 
                WHERE a.localizacao = ? AND a.ativo = 1";
        $params = [$localizacao];
        
        if ($postId) {
            $sql .= " AND EXISTS (SELECT 1 FROM anuncios_posts ap 
                                 WHERE ap.anuncio_id = a.id AND ap.post_id = ?)";
            $params[] = $postId;
        }
        
        $sql .= " ORDER BY RAND()";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar anúncios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Registrar clique em anúncio
     */
    public function registrarClique($anuncioId, $postId, $tipoClique) {
        $sql = "INSERT INTO cliques_anuncios (anuncio_id, post_id, tipo_clique, ip_usuario, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $anuncioId,
                $postId,
                $tipoClique,
                $this->getUserIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao registrar clique: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar todos os anúncios com estatísticas
     */
    public function getAllAnunciosComStats() {
        $sql = "SELECT a.*, 
                       COUNT(DISTINCT ca.id) as total_cliques,
                       COUNT(DISTINCT ap.post_id) as total_posts
                FROM anuncios a 
                LEFT JOIN cliques_anuncios ca ON a.id = ca.anuncio_id
                LEFT JOIN anuncios_posts ap ON a.id = ap.anuncio_id
                GROUP BY a.id 
                ORDER BY total_cliques DESC, a.criado_em DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar anúncios com stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar top 5 anúncios mais clicados
     */
    public function getTopAnuncios($limit = 5) {
        $limit = (int) $limit; // Converter para inteiro
        $sql = "SELECT a.*, COUNT(ca.id) as total_cliques
                FROM anuncios a 
                LEFT JOIN cliques_anuncios ca ON a.id = ca.anuncio_id
                WHERE a.ativo = 1
                GROUP BY a.id 
                ORDER BY total_cliques DESC 
                LIMIT $limit";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar top anúncios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo anúncio
     */
    public function criarAnuncio($dados) {
        $sql = "INSERT INTO anuncios (titulo, imagem, link_compra, localizacao, layout) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['titulo'],
                $dados['imagem'],
                $dados['link_compra'],
                $dados['localizacao'],
                $dados['layout'] ?? 'carrossel'
            ]);
            
            $anuncioId = $this->pdo->lastInsertId();
            
            // Associar aos posts selecionados
            if (!empty($dados['posts'])) {
                $this->associarAnuncioPosts($anuncioId, $dados['posts']);
            }
            
            return $anuncioId;
        } catch (Exception $e) {
            error_log("Erro ao criar anúncio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar anúncio
     */
    public function atualizarAnuncio($id, $dados) {
        $sql = "UPDATE anuncios SET 
                titulo = ?, imagem = ?, link_compra = ?, localizacao = ?, layout = ?,
                ativo = ?
                WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['titulo'],
                $dados['imagem'],
                $dados['link_compra'],
                $dados['localizacao'],
                $dados['layout'] ?? 'carrossel',
                $dados['ativo'] ?? true,
                $id
            ]);
            
            // Atualizar associações com posts
            if (isset($dados['posts'])) {
                $this->removerAssociacoesAnuncio($id);
                if (!empty($dados['posts'])) {
                    $this->associarAnuncioPosts($id, $dados['posts']);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar anúncio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir anúncio
     */
    public function excluirAnuncio($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM anuncios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir anúncio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar anúncio por ID
     */
    public function getAnuncio($id) {
        $sql = "SELECT a.*, GROUP_CONCAT(ap.post_id) as posts_ids
                FROM anuncios a 
                LEFT JOIN anuncios_posts ap ON a.id = ap.anuncio_id
                WHERE a.id = ?
                GROUP BY a.id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($anuncio && $anuncio['posts_ids']) {
                $anuncio['posts_ids'] = explode(',', $anuncio['posts_ids']);
            } else {
                $anuncio['posts_ids'] = [];
            }
            
            return $anuncio;
        } catch (Exception $e) {
            error_log("Erro ao buscar anúncio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar posts para seleção
     */
    public function getPostsParaSelecao() {
        $sql = "SELECT id, titulo FROM posts ORDER BY titulo";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar posts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Associar anúncio aos posts
     */
    private function associarAnuncioPosts($anuncioId, $postsIds) {
        $sql = "INSERT INTO anuncios_posts (anuncio_id, post_id) VALUES (?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($postsIds as $postId) {
                $stmt->execute([$anuncioId, $postId]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao associar anúncio aos posts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remover associações de anúncio
     */
    private function removerAssociacoesAnuncio($anuncioId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM anuncios_posts WHERE anuncio_id = ?");
            return $stmt->execute([$anuncioId]);
        } catch (Exception $e) {
            error_log("Erro ao remover associações: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter IP do usuário
     */
    private function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
    
    /**
     * Gerar HTML do anúncio
     */
    public function gerarHTMLAnuncio($anuncio) {
        // Corrigir caminho da imagem se necessário
        $imagem_path = $anuncio['imagem'];
        if (!empty($imagem_path) && !preg_match('/^https?:\/\//', $imagem_path)) {
            // Se não é uma URL externa, adicionar o caminho base
            $imagem_path = BLOG_URL . $imagem_path;
        }
        
        $html = '<div class="anuncio-card" data-anuncio-id="' . $anuncio['id'] . '">';
        $html .= '<div class="anuncio-patrocinado">Anúncio</div>';
        $html .= '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-link" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'imagem\')">';
        $html .= '<img src="' . htmlspecialchars($imagem_path) . '" alt="' . htmlspecialchars($anuncio['titulo']) . '" class="anuncio-imagem">';
        $html .= '</a>';
        $html .= '<div class="anuncio-conteudo">';
        $html .= '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-titulo" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'titulo\')">';
        $html .= htmlspecialchars($anuncio['titulo']);
        $html .= '</a>';
        
        // CTA removido no novo modelo
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
} 