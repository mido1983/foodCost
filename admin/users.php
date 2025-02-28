<?php
require_once '../includes/config.php';

$page_title = 'User Management';
$current_page = 'admin_users';

// Подключаем верхнюю часть шаблона
require_once '../includes/admin_header.php';

// Инициализация пользовательского класса
$userModel = new User();

// Получение всех пользователей
$users = $userModel->getAllUsers();

// Обработка удаления пользователя
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Проверка, не удаляет ли админ самого себя
    if ($user_id === (int)Session::get('user_id')) {
        Session::setFlash('error', 'You cannot delete your own account');
    } else {
        // Проверка, существует ли пользователь
        $user = $userModel->findById($user_id);
        if ($user) {
            // Проверка, не пытаются ли удалить главного админа
            if ($user['email'] === 'michael.doroshenko1@gmail.com') {
                Session::setFlash('error', 'Cannot delete main administrator account');
            } else {
                // Удаление пользователя
                $result = $userModel->delete($user_id);
                if ($result) {
                    Session::setFlash('success', 'User deleted successfully');
                    // Логирование действия администратора
                    logAdminAction('delete_user', 'user', $user_id);
                    // Перенаправление для обновления списка
                    header('Location: ' . SITE_URL . '/admin/users.php');
                    exit;
                } else {
                    Session::setFlash('error', 'Failed to delete user');
                }
            }
        } else {
            Session::setFlash('error', 'User not found');
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
    <a href="<?= SITE_URL ?>/admin/user_add.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Add New User
    </a>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/user_edit.php?id=<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['username']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['account_status'] === 'premium'): ?>
                                <span class="badge bg-success">Premium</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">Free</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/user_edit.php?id=<?= $user['id'] ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['email'] !== 'michael.doroshenko1@gmail.com' && $user['id'] !== Session::get('user_id')): ?>
                                <a href="<?= SITE_URL ?>/admin/users.php?action=delete&id=<?= $user['id'] ?>" 
                                   class="btn btn-danger btn-sm btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Подключаем нижнюю часть шаблона
require_once '../includes/admin_footer.php';
?> 