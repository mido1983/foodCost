<?php
require_once '../includes/config.php';

$page_title = 'Export Statistics';
$current_page = 'admin_stats';

$adminStats = new AdminStats();

// Определяем период для отчета
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$custom_start = isset($_GET['start']) ? $_GET['start'] : '';
$custom_end = isset($_GET['end']) ? $_GET['end'] : '';

// Экспорт в CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Установка заголовков для загрузки CSV файла
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=site_stats_' . date('Y-m-d') . '.csv');
    
    // Создание файлового указателя для вывода
    $output = fopen('php://output', 'w');
    
    // Заголовки CSV
    fputcsv($output, ['Date', 'Page Views', 'Unique Visitors', 'Registered Users', 'Premium Users', 'Total Revenue']);
    
    // Получаем и записываем данные в зависимости от периода
    $stats = getStatsByPeriod($period, $custom_start, $custom_end);
    
    foreach ($stats as $row) {
        fputcsv($output, [
            $row['date'],
            $row['page_views'],
            $row['unique_visitors'],
            $row['registered_users'],
            $row['premium_users'],
            number_format($row['total_revenue'], 2)
        ]);
    }
    
    fclose($output);
    exit;
}

// Функция для получения статистики за определенный период
function getStatsByPeriod($period, $custom_start = '', $custom_end = '') {
    global $adminStats;
    
    $today = date('Y-m-d');
    $start_date = '';
    $end_date = $today;
    
    switch ($period) {
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $start_date = date('Y-m-d', strtotime('-30 days'));
            break;
        case 'quarter':
            $start_date = date('Y-m-d', strtotime('-90 days'));
            break;
        case 'year':
            $start_date = date('Y-m-d', strtotime('-365 days'));
            break;
        case 'custom':
            if (!empty($custom_start)) {
                $start_date = date('Y-m-d', strtotime($custom_start));
            } else {
                $start_date = date('Y-m-d', strtotime('-30 days'));
            }
            
            if (!empty($custom_end)) {
                $end_date = date('Y-m-d', strtotime($custom_end));
            }
            break;
    }
    
    // Здесь должна быть реализация метода getStatsForPeriod в классе AdminStats
    // Возвращаем данные за указанный период
    return $adminStats->getStatsForPeriod($start_date, $end_date);
}

// Получаем статистику для текущего периода
$stats = getStatsByPeriod($period, $custom_start, $custom_end);

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Export Statistics</h1>
    <div>
        <a href="<?= SITE_URL ?>/admin/export_stats.php?export=csv&period=<?= $period ?>&start=<?= urlencode($custom_start) ?>&end=<?= urlencode($custom_end) ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Period Selection Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Select Period</h6>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="period" class="form-label">Predefined Periods</label>
                <select id="period" name="period" class="form-control" onchange="toggleCustomDates(this.value)">
                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="quarter" <?= $period === 'quarter' ? 'selected' : '' ?>>Last 90 Days</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Last 365 Days</option>
                    <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                </select>
            </div>
            
            <div id="custom-dates" class="row g-3 mt-0" style="<?= $period === 'custom' ? '' : 'display: none;' ?>">
                <div class="col-md-4">
                    <label for="start" class="form-label">Start Date</label>
                    <input type="date" id="start" name="start" class="form-control" value="<?= $custom_start ?>">
                </div>
                <div class="col-md-4">
                    <label for="end" class="form-label">End Date</label>
                    <input type="date" id="end" name="end" class="form-control" value="<?= $custom_end ?>">
                </div>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Statistics Data</h6>
    </div>
    <div class="card-body">
        <?php if (empty($stats)): ?>
            <p class="text-center">No statistics data available for the selected period.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Page Views</th>
                            <th>Unique Visitors</th>
                            <th>Registered Users</th>
                            <th>Premium Users</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $row): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                            <td><?= number_format($row['page_views']) ?></td>
                            <td><?= number_format($row['unique_visitors']) ?></td>
                            <td><?= number_format($row['registered_users']) ?></td>
                            <td><?= number_format($row['premium_users']) ?></td>
                            <td><?= '$' . number_format($row['total_revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCustomDates(value) {
    const customDatesDiv = document.getElementById('custom-dates');
    if (value === 'custom') {
        customDatesDiv.style.display = 'flex';
    } else {
        customDatesDiv.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/admin_footer.php'; ?> 