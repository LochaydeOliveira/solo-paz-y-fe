<?php
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/AnunciosManager.php';
require_once 'includes/auth.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$anunciosManager = new AnunciosManager($pdo);

// Buscar estatísticas
$anuncios = $anunciosManager->getAllAnunciosComStats();
$topAnuncios = $anunciosManager->getTopAnuncios(10);

// Estatísticas gerais
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cliques_anuncios");
$stmt->execute();
$totalCliques = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cliques_anuncios WHERE data_clique >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute();
$cliquesUltimaSemana = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cliques_anuncios WHERE data_clique >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$cliquesUltimoMes = $stmt->fetch()['total'];

$page_title = "Estatísticas de Anúncios";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Estatísticas de Anúncios Nativos</h1>
        </div>
    </div>

    <!-- Cards de Estatísticas Gerais -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Cliques
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalCliques); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mouse-pointer fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Última Semana
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($cliquesUltimaSemana); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Último Mês
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($cliquesUltimoMes); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Anúncios Ativos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count(array_filter($anuncios, function($a) { return $a['ativo']; })); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ad fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Anúncios com Estatísticas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Anúncios e Estatísticas</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Localização</th>
                                    <th>Status</th>
                                    <th>Total Cliques</th>
                                    <th>Posts Associados</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($anuncios as $anuncio): ?>
                                <tr>
                                    <td><?php echo $anuncio['id']; ?></td>
                                    <td><?php echo htmlspecialchars($anuncio['titulo']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $anuncio['localizacao'] === 'sidebar' ? 'info' : 'success'; ?>">
                                            <?php echo ucfirst($anuncio['localizacao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $anuncio['ativo'] ? 'success' : 'danger'; ?>">
                                            <?php echo $anuncio['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo number_format($anuncio['total_cliques']); ?></span>
                                    </td>
                                    <td><?php echo number_format($anuncio['total_posts']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($anuncio['criado_em'])); ?></td>
                                    <td>
                                        <a href="editar-anuncio.php?id=<?php echo $anuncio['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="detalhes-cliques.php?anuncio_id=<?php echo $anuncio['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Anúncios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Anúncios Mais Clicados</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($topAnuncios)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Título</th>
                                    <th>Localização</th>
                                    <th>Total Cliques</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topAnuncios as $index => $anuncio): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-<?php echo $index < 3 ? 'warning' : 'secondary'; ?>">
                                            #<?php echo $index + 1; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($anuncio['titulo']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $anuncio['localizacao'] === 'sidebar' ? 'info' : 'success'; ?>">
                                            <?php echo ucfirst($anuncio['localizacao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo number_format($anuncio['total_cliques']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $anuncio['ativo'] ? 'success' : 'danger'; ?>">
                                            <?php echo $anuncio['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Nenhum anúncio encontrado.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "order": [[4, "desc"]], // Ordenar por total de cliques
        "pageLength": 25,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 