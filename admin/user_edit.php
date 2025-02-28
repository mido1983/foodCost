<?php
require_once '../includes/config.php';

$page_title = 'Edit User';
$current_page = 'admin_users';

// Проверяем ID пользователя
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Session::setFlash('error', 'No user ID provided');
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

$user_id = (int)$_GET['id'];
$userModel = new User();
$user = $userModel->findById($user_id);

if (!$user) {
    Session::setFlash('error', 'User not found');
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $account_status = $_POST['account_status'];
    $new_password = trim($_POST['new_password']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Проверяем, не используется ли уже такой email другим пользователем
    $existingUser = $userModel->findByEmail($email);
    if ($existingUser && $existingUser['id'] != $user_id) {
        $errors[] = 'Email already in use by another account';
    }
    
    // Проверяем, не используется ли уже такой username другим пользователем
    $existingUser = $userModel->findByUsername($username);
    if ($existingUser && $existingUser['id'] != $user_id) {
        $errors[] = 'Username already in use by another account';
    }
    
    // Предотвращаем изменение роли для главного администратора
    if ($user['email'] === 'michael.doroshenko1@gmail.com' && $role !== 'admin') {
        $errors[] = 'Cannot change role of main administrator account';
    }
    
    if (empty($errors)) {
        $updateData = [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'account_status' => $account_status
        ];
        
        // Добавляем новый пароль, если он указан
        if (!empty($new_password)) {
            $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        if ($userModel->updateProfile($user_id, $updateData)) {
            logAdminAction('update_user', 'user', $user_id);
            Session::setFlash('success', 'User updated successfully');
            header('Location: ' . SITE_URL . '/admin/users.php');
            exit;
        } else {
            $errors[] = 'Failed to update user';
        }
    }
}

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
    <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?= $error ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control" <?= $user['email'] === 'michael.doroshenko1@gmail.com' ? 'disabled' : '' ?>>
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                        <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                    <?php if ($user['email'] === 'michael.doroshenko1@gmail.com'): ?>
                    <input type="hidden" name="role" value="admin">
                    <small class="text-muted">Main admin role cannot be changed.</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="account_status" class="form-label">Account Status</label>
                    <select id="account_status" name="account_status" class="form-control">
                        <option value="free" <?= $user['account_status'] === 'free' ? 'selected' : '' ?>>Free</option>
                        <option value="premium" <?= $user['account_status'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" id="new_password" name="new_password" class="form-control">
                <small class="form-text text-muted">Minimum 8 characters recommended.</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Registration Date</label>
                <p class="form-control-static"><?= date('F j, Y g:i a', strtotime($user['created_at'])) ?></p>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 