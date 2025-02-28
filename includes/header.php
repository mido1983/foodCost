<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="<?= SITE_URL ?>"><?= SITE_NAME ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>" href="<?= SITE_URL ?>">Home</a>
                        </li>
                        <!-- Add more menu items here -->
                    </ul>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <?php if (Session::isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> <?= Session::get('username') ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php">My Profile</a></li>
                                    <?php if (Session::get('account_status') === 'free'): ?>
                                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/payment.php">Upgrade Account</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                                </ul>
                            </li>
                            <?php 
                            $user = new User();
                            $userRole = $user->getRole();
                            if ($userRole === 'admin'): 
                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" 
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cogs"></i> Администрирование
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/index.php">Панель управления</a></li>
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/users.php">Управление пользователями</a></li>
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/blog.php">Управление блогом</a></li>
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/export_stats.php">Статистика и экспорт</a></li>
                                </ul>
                            </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page === 'login' ? 'active' : '' ?>" href="<?= SITE_URL ?>/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page === 'register' ? 'active' : '' ?>" href="<?= SITE_URL ?>/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container py-4">
        <?php 
        $flash = Session::getFlash();
        if ($flash): 
        ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?> 