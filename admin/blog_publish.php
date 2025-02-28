<?php
require_once '../includes/config.php';

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Session::setFlash('error', 'No post ID provided');
    header('Location: ' . SITE_URL . '/admin/blog.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Create blog instance
$blog = new Blog();

// Get post data
$post = $blog->getPostById($post_id);
if (!$post) {
    Session::setFlash('error', 'Post not found');
    header('Location: ' . SITE_URL . '/admin/blog.php');
    exit;
}

// Update post status to published
if ($blog->updatePost($post_id, ['status' => 'published'])) {
    // Log admin action
    logAdminAction('publish_post', 'post', $post_id);
    
    Session::setFlash('success', 'Post published successfully');
} else {
    Session::setFlash('error', 'Failed to publish post');
}

// Redirect back to blog management
header('Location: ' . SITE_URL . '/admin/blog.php');
exit; 