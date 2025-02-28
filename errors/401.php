<?php
require_once '../includes/config.php';
$page_title = 'Требуется авторизация';
http_response_code(401);
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-warning">401</h1>
            <p class="fs-3 mb-4">Требуется авторизация</p>
            <p class="lead mb-5">
                Для доступа к запрошенной странице необходимо войти в систему.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>/login" class="btn btn-primary">Войти</a>
                <a href="<?= SITE_URL ?>" class="btn btn-outline-secondary">Вернуться на главную</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 