<?php
/*
 * Универсальная страница ошибки
 * Параметры: 
 * - code: HTTP код ошибки (404, 500 и т.д.)
 * - title: Заголовок ошибки
 * - message: Подробное сообщение
 * - class: CSS класс для цвета (primary, danger, warning)
 */

// Параметры по умолчанию
$error_code = $_GET['code'] ?? 404;
$error_title = $_GET['title'] ?? 'Ошибка';
$error_message = $_GET['message'] ?? 'Произошла неизвестная ошибка.';
$error_class = $_GET['class'] ?? 'primary';

// HTTP-код ответа
http_response_code($error_code);

require_once '../includes/config.php';
$page_title = $error_title;
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 fw-bold text-<?= $error_class ?>"><?= $error_code ?></h1>
            <p class="fs-3 mb-4"><?= htmlspecialchars($error_title) ?></p>
            <p class="lead mb-5">
                <?= htmlspecialchars($error_message) ?>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>" class="btn btn-primary">Вернуться на главную</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">Вернуться назад</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 