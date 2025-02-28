<?php
class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createPayment($userId, $amount, $paymentMethod) {
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending'
        ];

        return $this->db->insert('payments', $data);
    }

    public function updatePaymentStatus($paymentId, $status) {
        return $this->db->update('payments', ['payment_status' => $status], ['id' => $paymentId]);
    }

    public function getPaymentsByUser($userId) {
        return $this->db->select('SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC', [$userId]);
    }

    public function getPaymentById($paymentId) {
        return $this->db->selectOne('SELECT * FROM payments WHERE id = ?', [$paymentId]);
    }

    public function processPayment($userId, $amount, $paymentMethod) {
        // Create a payment record
        $paymentId = $this->createPayment($userId, $amount, $paymentMethod);
        
        if (!$paymentId) {
            return ['success' => false, 'message' => 'Failed to create payment record'];
        }

        // Simulate payment processing
        // In a real application, you would integrate with a payment gateway here
        $paymentSuccess = true; // Simulating successful payment
        
        if ($paymentSuccess) {
            // Update payment status to completed
            $this->updatePaymentStatus($paymentId, 'completed');
            
            // Upgrade user account
            $user = new User();
            $user->upgradeAccount($userId, 'premium');
            
            return ['success' => true, 'payment_id' => $paymentId];
        } else {
            // Update payment status to failed
            $this->updatePaymentStatus($paymentId, 'failed');
            
            return ['success' => false, 'message' => 'Payment processing failed', 'payment_id' => $paymentId];
        }
    }
} 