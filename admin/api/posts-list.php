<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/db.php';
require_once '../includes/auth.php';

check_login();

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 20;
$offset = ($page - 1) * $perPage;

try {
	$baseSql = "FROM posts p WHERE p.publicado = 1";
	$params = [];
	if ($q !== '') {
		$baseSql .= ' AND p.titulo LIKE ?';
		$params[] = '%' . $q . '%';
	}

	$stmt = $pdo->prepare('SELECT COUNT(*) AS total ' . $baseSql);
	$stmt->execute($params);
	$total = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

	$stmt = $pdo->prepare('SELECT p.id, p.titulo, p.slug, p.data_publicacao ' . $baseSql . ' ORDER BY p.data_publicacao DESC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset);
	$stmt->execute($params);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode([
		'data' => $rows,
		'total' => $total,
		'page' => $page,
		'per_page' => $perPage
	]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Erro ao carregar posts', 'message' => $e->getMessage()]);
}
