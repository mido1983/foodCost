<?php
require_once '../includes/config.php';
$page_title = 'Сервис временно недоступен';
http_response_code(503);
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-warning">503</h1>
            <p class="fs-3 mb-4">Сервис временно недоступен</p>
            <p class="lead mb-5">
                В настоящее время сервис недоступен из-за технических работ. 
                Пожалуйста, попробуйте зайти позже.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>" class="btn btn-primary">Вернуться на главную</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">Вернуться назад</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 