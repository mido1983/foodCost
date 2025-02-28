<?php
require_once '../includes/config.php';
$page_title = 'Страница не найдена';
http_response_code(404);
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-primary">404</h1>
            <p class="fs-3 mb-4">Страница не найдена</p>
            <p class="lead mb-5">
                К сожалению, страница, которую вы ищете, не существует, была удалена или временно недоступна.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>" class="btn btn-primary">Вернуться на главную</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">Вернуться назад</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 