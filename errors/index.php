<?php
// Редирект на главную, если кто-то перешел напрямую в папку errors
header('Location: ' . SITE_URL);
exit;
?> 