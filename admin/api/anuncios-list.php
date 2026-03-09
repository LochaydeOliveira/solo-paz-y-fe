<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/db.php';
require_once '../includes/auth.php';

// Apenas usuários logados
check_login();

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$marca = $_GET['marca'] ?? '';
$status = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 20;
$offset = ($page - 1) * $perPage;

try {
	$baseSql = "FROM anuncios a WHERE 1=1";
	$conds = [];
	$params = [];

	if ($q !== '') {
		$conds[] = '(a.titulo LIKE ? OR a.link_compra LIKE ?)';
		$params[] = '%' . $q . '%';
		$params[] = '%' . $q . '%';
	}
	if ($marca !== '') {
		$conds[] = 'a.marca = ?';
		$params[] = $marca;
	}
	if ($status === '1' || $status === '0') {
		$conds[] = 'a.ativo = ?';
		$params[] = (int)$status;
	}

	if (!empty($conds)) {
		$baseSql .= ' AND ' . implode(' AND ', $conds);
	}

	$stmt = $pdo->prepare('SELECT COUNT(*) AS total ' . $baseSql);
	$stmt->execute($params);
	$total = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

	$stmt = $pdo->prepare('SELECT a.id, a.titulo, a.marca, a.ativo, a.imagem ' . $baseSql . ' ORDER BY a.titulo ASC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset);
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
	echo json_encode(['error' => 'Erro ao carregar anúncios', 'message' => $e->getMessage()]);
}
