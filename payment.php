<?php
$current_page = 'payment';
$page_title = 'Upgrade Account';
require_once 'includes/config.php';

// Require user to be logged in
Session::requireLogin();

// Redirect if already premium
$user_id = Session::get('user_id');
$user = new User();
$user_data = $user->findById($user_id);

if ($user_data['account_status'] === 'premium') {
    Session::setFlash('info', 'Your account is already premium!');
    header('Location: ' . SITE_URL . '/profile.php');
    exit;
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = $_POST['selected_plan'] ?? 'monthly';
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method';
    }
    
    // Set price based on plan
    $amount = ($plan === 'annual') ? 99.99 : 9.99;
    
    // If no validation errors, process payment
    if (empty($errors)) {
        $payment = new Payment();
        $result = $payment->processPayment($user_id, $amount, $payment_method);
        
        if ($result['success']) {
            // Set success message and redirect
            Session::setFlash('success', 'Payment successful! Your account has been upgraded to premium.');
            header('Location: ' . SITE_URL . '/profile.php');
            exit;
        } else {
            $error_message = $result['message'];
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="mb-4">Upgrade Your Account</h1>
            
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
            
            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Choose Your Premium Plan</h3>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" id="selected_plan" name="selected_plan" value="monthly">
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="payment-option text-center h-100 selected" data-plan="monthly">
                                    <h4>Monthly Plan</h4>
                                    <h2 class="text-primary mb-3">$9.99<small class="text-muted">/month</small></h2>
                                    <ul class="list-unstyled">
                                        <li>Full Access to Premium Features</li>
                                        <li>Priority Support</li>
                                        <li>Cancel Anytime</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-option text-center h-100" data-plan="annual">
                                    <h4>Annual Plan</h4>
                                    <h2 class="text-primary mb-3">$99.99<small class="text-muted">/year</small></h2>
                                    <div class="badge bg-success mb-2">Save 16%</div>
                                    <ul class="list-unstyled">
                                        <li>All Monthly Plan Features</li>
                                        <li>Additional Premium Content</li>
                                        <li>Best Value</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h3 class="mb-3">Payment Method</h3>
                        
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card" required>
                                <label class="form-check-label" for="card">
                                    <i class="fab fa-cc-visa me-2"></i><i class="fab fa-cc-mastercard me-2"></i><i class="fab fa-cc-amex"></i> Credit Card
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <i class="fab fa-paypal me-2"></i> PayPal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer">
                                <label class="form-check-label" for="bank">
                                    <i class="fas fa-university me-2"></i> Bank Transfer
                                </label>
                            </div>
                            <div class="invalid-feedback">Please select a payment method.</div>
                        </div>
                        
                        <!-- Credit Card Details - shown conditionally with JavaScript -->
                        <div id="card-details" class="mt-4 d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="card_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry" class="form-label">Expiration Date</label>
                                    <input type="text" class="form-control" id="expiry" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="XXX">
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Complete Payment</button>
                        </div>
                        <p class="text-center text-muted mt-3">
                            <small>By completing your purchase, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</small>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide credit card details based on payment method selection
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('card-details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                cardDetails.classList.remove('d-none');
            } else {
                cardDetails.classList.add('d-none');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 