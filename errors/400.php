<?php
require_once '../includes/config.php';
$page_title = 'Некорректный запрос';
http_response_code(400);
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-warning">400</h1>
            <p class="fs-3 mb-4">Некорректный запрос</p>
            <p class="lead mb-5">
                Ваш запрос не может быть обработан из-за синтаксической ошибки.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>" class="btn btn-primary">Вернуться на главную</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">Вернуться назад</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 