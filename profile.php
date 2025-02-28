<?php
$current_page = 'profile';
$page_title = 'My Profile';
require_once 'includes/config.php';

// Require user to be logged in
Session::requireLogin();

$user_id = Session::get('user_id');
$user = new User();
$user_data = $user->findById($user_id);

// Handle profile update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $errors = [];
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if email changed and already exists
    if ($email !== $user_data['email']) {
        $existing_user = $user->findByEmail($email);
        if ($existing_user) {
            $errors[] = 'Email already in use by another account';
        }
    }
    
    // Handle password change if requested
    if (!empty($current_password)) {
        // Verify current password
        if (!password_verify($current_password, $user_data['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        if (empty($new_password)) {
            $errors[] = 'New password is required';
        } elseif (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        }
    }
    
    // If no validation errors, update profile
    if (empty($errors)) {
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];
        
        // Update password if changed
        if (!empty($new_password)) {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $result = $user->updateProfile($user_id, $update_data);
        
        if ($result) {
            // Refresh user data
            $user_data = $user->findById($user_id);
            
            // Set success message
            Session::setFlash('success', 'Profile updated successfully');
            
            // Redirect to avoid form resubmission
            header('Location: ' . SITE_URL . '/profile.php');
            exit;
        } else {
            $error_message = 'Failed to update profile';
        }
    }
}

// Get payment history
$payment = new Payment();
$payment_history = $payment->getPaymentsByUser($user_id);

require_once 'includes/header.php';
?>

<div class="profile-header bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <img src="https://via.placeholder.com/150" alt="Profile Image" class="profile-img">
            </div>
            <div class="col-md-9">
                <h1><?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></h1>
                <p class="lead mb-0">@<?= htmlspecialchars($user_data['username']) ?></p>
                <p class="text-muted">Member since <?= date('F j, Y', strtotime($user_data['created_at'])) ?></p>
                <div class="badge bg-<?= $user_data['account_status'] === 'premium' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($user_data['account_status']) ?> Account
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Account Type
                            <span class="badge bg-<?= $user_data['account_status'] === 'premium' ? 'success' : 'secondary' ?> rounded-pill">
                                <?= ucfirst($user_data['account_status']) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Email
                            <span><?= htmlspecialchars($user_data['email']) ?></span>
                        </li>
                    </ul>
                </div>
                <?php if ($user_data['account_status'] === 'free'): ?>
                <div class="card-footer text-center">
                    <a href="<?= SITE_URL ?>/payment.php" class="btn btn-primary">Upgrade to Premium</a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($payment_history)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($payment_history as $payment): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>$<?= number_format($payment['amount'], 2) ?></span>
                                <span class="badge bg-<?= $payment['payment_status'] === 'completed' ? 'success' : ($payment['payment_status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($payment['payment_status']) ?>
                                </span>
                            </div>
                            <small class="text-muted"><?= date('M j, Y', strtotime($payment['payment_date'])) ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user_data['first_name']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user_data['last_name']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        
                        <hr class="my-4">
                        <h5>Change Password</h5>
                        <p class="text-muted">Leave blank if you don't want to change your password</p>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Password must be at least 8 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 