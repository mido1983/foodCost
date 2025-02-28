<?php
$current_page = 'home';
$page_title = 'Home';
require_once 'includes/header.php';
?>

<section class="hero text-center">
    <div class="container">
        <h1>Welcome to <?= SITE_NAME ?></h1>
        <p class="lead">Your journey to success starts here</p>
        <div class="mt-4">
            <?php if (!Session::isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg me-2">Get Started</a>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light btn-lg">Login</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/profile.php" class="btn btn-primary btn-lg">My Profile</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="text-center mb-5">Our Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-rocket fa-3x mb-3 text-primary"></i>
                        <h3 class="card-title">Fast Performance</h3>
                        <p class="card-text">Experience lightning-fast performance with our optimized platform.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                        <h3 class="card-title">Secure System</h3>
                        <p class="card-text">Your data is protected with state-of-the-art security measures.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-headset fa-3x mb-3 text-primary"></i>
                        <h3 class="card-title">24/7 Support</h3>
                        <p class="card-text">Our dedicated support team is always ready to help you.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pricing py-5 mt-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Choose Your Plan</h2>
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-5 mb-lg-0">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase text-center">Free</h5>
                        <h6 class="card-price text-center">$0<span class="period">/month</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Single User</li>
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>5GB Storage</li>
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Basic Support</li>
                            <li class="text-muted"><span class="fa-li"><i class="fas fa-times"></i></span>Advanced Features</li>
                        </ul>
                        <div class="d-grid">
                            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary text-uppercase">Sign Up For Free</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase text-center">Premium</h5>
                        <h6 class="card-price text-center">$9.99<span class="period">/month</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Up to 5 Users</li>
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>50GB Storage</li>
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Priority Support</li>
                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Advanced Features</li>
                        </ul>
                        <div class="d-grid">
                            <a href="<?= SITE_URL ?>/payment.php" class="btn btn-primary text-uppercase">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 