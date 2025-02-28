<?php
require_once 'includes/config.php';

// Log the user out
$user = new User();
$user->logout();

// Redirect to homepage with message
Session::setFlash('info', 'You have been logged out successfully');
header('Location: ' . SITE_URL);
exit; 