<?php
require_once 'includes/config.php';

// Get post by slug
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

$blog = new Blog();
$post = $blog->getPostBySlug($slug);

if (!$post) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

// Get post categories
$categories = $blog->getPostCategories($post['id']);

// Get comments
$comments = $blog->getComments($post['id']);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Session::isLoggedIn()) {
    $content = trim($_POST['comment'] ?? '');
    
    if (!empty($content)) {
        $user_id = Session::get('user_id');
        $blog->addComment($post['id'], $user_id, $content);
        Session::setFlash('success', 'Your comment has been submitted and is awaiting approval.');
        header('Location: ' . SITE_URL . '/blog-post.php?slug=' . $slug);
        exit;
    }
}

$current_page = 'blog';
$page_title = $post['title'];
require_once 'includes/header.php';

// Проверяем, содержит ли путь к изображению уже полный URL
$imageUrl = $post['featured_image'];
if (!preg_match('/^https?:\/\//', $imageUrl)) {
    // Если нет, добавляем путь к директории загрузок
    $imageUrl = UPLOADS_URL . '/blog/' . $imageUrl;
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <article class="blog-post">
                <h1 class="mb-3"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="d-flex mb-4 text-muted">
                    <div class="me-3">
                        <i class="fas fa-user me-1"></i> 
                        <?= !empty($post['first_name']) && !empty($post['last_name']) 
                            ? htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) 
                            : htmlspecialchars($post['author_name']) ?>
                    </div>
                    <div class="me-3">
                        <i class="fas fa-calendar-alt me-1"></i> 
                        <?= date('F j, Y', strtotime($post['created_at'])) ?>
                    </div>
                    <?php if (!empty($categories)): ?>
                        <div>
                            <i class="fas fa-tags me-1"></i>
                            <?php foreach ($categories as $index => $category): ?>
                                <a href="<?= SITE_URL ?>/blog.php?category=<?= $category['slug'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                                <?= ($index < count($categories) - 1) ? ', ' : '' ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="mb-4">
                        <img src="<?= $imageUrl ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($post['title']) ?>">
                    </div>
                <?php endif; ?>
                
                <div class="blog-content mb-5">
                    <?= nl2br(htmlspecialchars_decode($post['content'])) ?>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h3 class="h5 mb-0">Share This Post</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/blog-post.php?slug=' . $slug) ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/blog-post.php?slug=' . $slug) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="btn btn-outline-info">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(SITE_URL . '/blog-post.php?slug=' . $slug) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode('Check out this article: ' . SITE_URL . '/blog-post.php?slug=' . $slug) ?>" class="btn btn-outline-success">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h3 class="h5 mb-0">Comments (<?= count($comments) ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($comment['username']) ?></h6>
                                            <small class="text-muted ms-2">
                                                <?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?>
                                            </small>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (Session::isLoggedIn()): ?>
                            <hr class="my-4">
                            <h4 class="h6 mb-3">Leave a Comment</h4>
                            <form method="POST">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="4" placeholder="Write your comment here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Comment</button>
                            </form>
                        <?php else: ?>
                            <hr class="my-4">
                            <div class="alert alert-info">
                                Please <a href="<?= SITE_URL ?>/login.php">login</a> to leave a comment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 