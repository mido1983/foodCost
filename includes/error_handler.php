<?php
/**
 * Перехват ошибок PHP и преобразование их в страницы ошибок
 */

// Обработчик исключений
function exception_handler($exception) {
    $error_code = 500;
    $error_message = $exception->getMessage();
    
    // Логирование ошибки
    error_log("Exception: {$error_message} in {$exception->getFile()} on line {$exception->getLine()}");
    
    // Перенаправление на страницу ошибки
    if (!headers_sent()) {
        header("Location: " . SITE_URL . "/errors/{$error_code}.php");
        exit;
    } else {
        echo "<p>Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.</p>";
    }
}

// Установить обработчик исключений
set_exception_handler('exception_handler');

// Обработчик фатальных ошибок
function fatal_error_handler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Логирование ошибки
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        // Перенаправление на страницу ошибки
        if (!headers_sent()) {
            header("Location: " . SITE_URL . "/errors/500.php");
            exit;
        } else {
            echo "<p>Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.</p>";
        }
    }
}

// Регистрация обработчика фатальных ошибок
register_shutdown_function('fatal_error_handler'); 