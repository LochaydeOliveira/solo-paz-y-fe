<?php

define('SEARCH_RESULTS_PER_PAGE', 12);
define('SEARCH_EXCERPT_LENGTH', 150);
define('SEARCH_HISTORY_LIMIT', 5);
define('SEARCH_SUGGESTIONS_LIMIT', 5);

function clean_search_term($term) {
    $term = trim($term);
    $term = strip_tags($term);
    $term = htmlspecialchars($term, ENT_QUOTES, 'UTF-8');
    return $term;
}

function generate_excerpt($content, $length = SEARCH_EXCERPT_LENGTH) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

function highlight_search_term($text, $term) {
    if (empty($term)) return $text;
    $pattern = '/(' . preg_quote($term, '/') . ')/i';
    return preg_replace($pattern, '<mark>$1</mark>', $text);
}

function save_search_history($term) {
    if (empty($term)) return;

    $history = isset($_SESSION['search_history']) ? $_SESSION['search_history'] : [];
    array_unshift($history, $term);
    $history = array_unique($history);
    $history = array_slice($history, 0, SEARCH_HISTORY_LIMIT);
    $_SESSION['search_history'] = $history;
}

function get_search_suggestions($term) {
    global $pdo;

    try {
        $sql = "SELECT DISTINCT titulo 
                FROM posts 
                WHERE publicado = 1 
                AND titulo LIKE :term 
                LIMIT " . SEARCH_SUGGESTIONS_LIMIT;

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['term' => '%' . $term . '%']);

        $suggestions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $suggestions[] = $row['titulo'];
        }

        return $suggestions;
    } catch (PDOException $e) {
        error_log("Erro ao buscar sugestões: " . $e->getMessage());
        return [];
    }
}

function generate_pagination($total_results, $current_page, $results_per_page) {
    $total_pages = ceil($total_results / $results_per_page);
    if ($total_pages <= 1) return '';

    $html = '<nav aria-label="Navegação da busca"><ul class="pagination justify-content-center">';

    $prev_disabled = $current_page <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item' . $prev_disabled . '">';
    $html .= '<a class="page-link" href="?q=' . urlencode($_GET['q']) . '&pagina=' . ($current_page - 1) . '" aria-label="Anterior">';
    $html .= '<span aria-hidden="true">&laquo;</span></a></li>';

    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $current_page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="?q=' . urlencode($_GET['q']) . '&pagina=' . $i . '">' . $i . '</a></li>';
    }

    $next_disabled = $current_page >= $total_pages ? ' disabled' : '';
    $html .= '<li class="page-item' . $next_disabled . '">';
    $html .= '<a class="page-link" href="?q=' . urlencode($_GET['q']) . '&pagina=' . ($current_page + 1) . '" aria-label="Próximo">';
    $html .= '<span aria-hidden="true">&raquo;</span></a></li>';

    $html .= '</ul></nav>';
    return $html;
}
