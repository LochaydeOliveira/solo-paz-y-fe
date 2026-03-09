<?php

class GruposAnunciosManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Buscar grupos de anúncios por localização
     */
    public function getGruposPorLocalizacao($localizacao, $postId = null, $isHomePage = false) {
        // Regra: anúncios nativos só aparecem em posts específicos selecionados
        if ($postId === null) {
            return [];
        }

        $sql = "SELECT g.*, COUNT(gi.anuncio_id) as total_anuncios
                FROM grupos_anuncios g 
                LEFT JOIN grupos_anuncios_items gi ON g.id = gi.grupo_id
                WHERE g.localizacao = ? AND g.ativo = 1
                AND g.id IN (
                    SELECT gap.grupo_id FROM grupos_anuncios_posts gap WHERE gap.post_id = ?
                )
                GROUP BY g.id ORDER BY g.criado_em DESC";

        $params = [$localizacao, $postId];
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar grupos de anúncios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar anúncios de um grupo específico
     */
    public function getAnunciosDoGrupo($grupoId) {
        $sql = "SELECT a.*, gi.ordem, gi.anuncio_id AS anuncio_id
                FROM anuncios a 
                JOIN grupos_anuncios_items gi ON a.id = gi.anuncio_id
                WHERE gi.grupo_id = ? AND a.ativo = 1
                ORDER BY gi.ordem ASC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$grupoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar anúncios do grupo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo grupo de anúncios
     */
    public function criarGrupo($dados) {
        // Sidebar força layout 'grade'; sempre posts_especificos = 1 e aparecer_inicio = 0
        $localizacao = $dados['localizacao'];
        $layout = ($localizacao === 'sidebar') ? 'grade' : ($dados['layout'] ?? 'carrossel');
        $ativo = !empty($dados['ativo']) ? 1 : 0;

        $sql = "INSERT INTO grupos_anuncios (nome, localizacao, layout, marca, posts_especificos, aparecer_inicio, ativo, criado_em) VALUES (?, ?, ?, '', 1, 0, ?, NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['nome'],
                $localizacao,
                $layout,
                $ativo
            ]);

            $grupoId = $this->pdo->lastInsertId();

            if (!empty($dados['anuncios'])) {
                $this->associarAnunciosAoGrupo($grupoId, $dados['anuncios']);
            }

            if (!empty($dados['posts'])) {
                $this->associarPostsAoGrupo($grupoId, $dados['posts']);
            }

            return $grupoId;
        } catch (Exception $e) {
            error_log("Erro ao criar grupo de anúncios: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Associar anúncios ao grupo
     */
    private function associarAnunciosAoGrupo($grupoId, $anunciosIds) {
        $sql = "INSERT INTO grupos_anuncios_items (grupo_id, anuncio_id, ordem) VALUES (?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($anunciosIds as $ordem => $anuncioId) {
                $stmt->execute([$grupoId, $anuncioId, $ordem]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao associar anúncios ao grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar todos os grupos com estatísticas
     */
    public function getAllGruposComStats() {
        $sql = "SELECT g.*, COUNT(gi.anuncio_id) as total_anuncios
                FROM grupos_anuncios g 
                LEFT JOIN grupos_anuncios_items gi ON g.id = gi.grupo_id
                GROUP BY g.id 
                ORDER BY g.criado_em DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar grupos com stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar grupo por ID
     */
    public function getGrupo($id) {
        $sql = "SELECT g.*
                FROM grupos_anuncios g 
                WHERE g.id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$grupo) {
                return false;
            }
            
            return $grupo;
        } catch (Exception $e) {
            error_log("Erro ao buscar grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar grupo
     */
    public function atualizarGrupo($id, $dados) {
        // Sidebar força layout 'grade'; sempre posts_especificos = 1 e aparecer_inicio = 0; marca não é configurável
        $localizacao = $dados['localizacao'];
        $layout = ($localizacao === 'sidebar') ? 'grade' : ($dados['layout'] ?? 'carrossel');
        $ativo = !empty($dados['ativo']) ? 1 : 0;

        $sql = "UPDATE grupos_anuncios SET 
                nome = ?, localizacao = ?, layout = ?, marca = '', ativo = ?, posts_especificos = 1, aparecer_inicio = 0
                WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['nome'],
                $localizacao,
                $layout,
                $ativo,
                $id
            ]);

            if (isset($dados['anuncios'])) {
                $this->removerAssociacoesGrupo($id);
                if (!empty($dados['anuncios'])) {
                    $this->associarAnunciosAoGrupo($id, $dados['anuncios']);
                }
            }

            if (isset($dados['posts'])) {
                $this->associarPostsAoGrupo($id, $dados['posts']);
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remover associações do grupo
     */
    private function removerAssociacoesGrupo($grupoId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM grupos_anuncios_items WHERE grupo_id = ?");
            return $stmt->execute([$grupoId]);
        } catch (Exception $e) {
            error_log("Erro ao remover associações do grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir grupo
     */
    public function excluirGrupo($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM grupos_anuncios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar posts associados a um grupo
     */
    public function getPostsDoGrupo($grupoId) {
        $sql = "SELECT p.id, p.titulo, p.slug 
                FROM posts p 
                JOIN grupos_anuncios_posts gap ON p.id = gap.post_id
                WHERE gap.grupo_id = ? AND p.publicado = 1
                ORDER BY p.titulo ASC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$grupoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar posts do grupo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar todos os posts disponíveis para seleção
     */
    public function getAllPosts() {
        $sql = "SELECT id, titulo, slug, data_publicacao 
                FROM posts 
                WHERE publicado = 1 
                ORDER BY titulo ASC";
        
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
     * Associar posts a um grupo
     */
    public function associarPostsAoGrupo($grupoId, $postsIds) {
        // Primeiro, remover todas as associações existentes
        $this->removerPostsDoGrupo($grupoId);
        
        if (empty($postsIds)) {
            return true;
        }
        
        $sql = "INSERT INTO grupos_anuncios_posts (grupo_id, post_id) VALUES (?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($postsIds as $postId) {
                $stmt->execute([$grupoId, $postId]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao associar posts ao grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remover todas as associações de posts de um grupo
     */
    public function removerPostsDoGrupo($grupoId) {
        $sql = "DELETE FROM grupos_anuncios_posts WHERE grupo_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$grupoId]);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao remover posts do grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar configurações de posts de um grupo
     */
    public function atualizarConfiguracoesPosts($grupoId, $postsEspecificos, $aparecerInicio, $postsIds = []) {
        // Nova regra: sempre posts_especificos = 1, aparecer_inicio = 0
        $sql = "UPDATE grupos_anuncios SET posts_especificos = 1, aparecer_inicio = 0 WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$grupoId]);

            // Associar posts selecionados (obrigatório)
            $this->associarPostsAoGrupo($grupoId, $postsIds);

            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar configurações de posts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Debug: Verificar configurações de um grupo
     */
    public function debugGrupo($grupoId) {
        $sql = "SELECT g.*, 
                       COUNT(gap.post_id) as total_posts_especificos,
                       GROUP_CONCAT(p.titulo SEPARATOR ', ') as posts_titulos
                FROM grupos_anuncios g 
                LEFT JOIN grupos_anuncios_posts gap ON g.id = gap.grupo_id
                LEFT JOIN posts p ON gap.post_id = p.id
                WHERE g.id = ?
                GROUP BY g.id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$grupoId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao debug grupo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Debug: Verificar se um grupo deve aparecer em um post específico
     */
    public function debugGrupoParaPost($grupoId, $postId, $isHomePage = false) {
        $grupo = $this->getGrupo($grupoId);
        if (!$grupo) return false;
        
        $result = [
            'grupo_id' => $grupoId,
            'post_id' => $postId,
            'is_home_page' => $isHomePage,
            'posts_especificos' => $grupo['posts_especificos'],
            'aparecer_inicio' => $grupo['aparecer_inicio'],
            'deve_aparecer' => false,
            'motivo' => ''
        ];
        
        // Se é página inicial e não deve aparecer na inicial
        if ($isHomePage && !$grupo['aparecer_inicio']) {
            $result['motivo'] = 'Não aparece na página inicial';
            return $result;
        }
        
        // Se não é posts específicos, aparece em todos
        if (!$grupo['posts_especificos']) {
            $result['deve_aparecer'] = true;
            $result['motivo'] = 'Aparece em todos os posts';
            return $result;
        }
        
        // Se é posts específicos, verificar se está na lista
        $postsDoGrupo = $this->getPostsDoGrupo($grupoId);
        $postsIds = array_column($postsDoGrupo, 'id');
        
        if (in_array($postId, $postsIds)) {
            $result['deve_aparecer'] = true;
            $result['motivo'] = 'Post está na lista de posts específicos';
        } else {
            $result['motivo'] = 'Post não está na lista de posts específicos';
        }
        
        return $result;
    }
}