<?php
require_once '../includes/config.php';
$page_title = 'Внутренняя ошибка сервера';
http_response_code(500);
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-danger">500</h1>
            <p class="fs-3 mb-4">Внутренняя ошибка сервера</p>
            <p class="lead mb-5">
                Произошла ошибка при обработке вашего запроса. Мы уже работаем над её устранением.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>" class="btn btn-primary">Вернуться на главную</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">Вернуться назад</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 