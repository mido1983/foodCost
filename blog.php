<?php
$current_page = 'blog';
$page_title = 'Blog';
require_once 'includes/config.php';

// Pagination
$limit = 6; // posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get blog posts
$blog = new Blog();
$posts = $blog->getAllPosts($limit, $offset);
$total_posts = $blog->countTotalPosts('published');
$total_pages = ceil($total_posts / $limit);

// Get categories for sidebar
$categories = $blog->getAllCategories();

// Проверяем, содержит ли путь к изображению уже полный URL
foreach ($posts as &$post) {
    if (!empty($post['featured_image'])) {
        if (!preg_match('/^https?:\/\//', $post['featured_image']) && !preg_match('/^uploads\//', $post['featured_image'])) {
            // Если нет, добавляем путь к директории загрузок
            $post['featured_image'] = 'uploads/blog/' . $post['featured_image'];
        }
    }
}
unset($post); // Разрываем ссылку на последний элемент

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Blog</h1>
    
    <div class="row">
        <div class="col-lg-8">
            <?php if (empty($posts)): ?>
                <div class="alert alert-info">No posts found. Check back soon!</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="<?= SITE_URL . '/' . $post['featured_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($post['author_name']) ?>
                                        <i class="fas fa-calendar-alt ms-2 me-1"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                    </div>
                                    <p class="card-text"><?= htmlspecialchars($post['excerpt']) ?></p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= $post['slug'] ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Blog pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= SITE_URL ?>/blog.php?page=<?= $page - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= SITE_URL ?>/blog.php?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= SITE_URL ?>/blog.php?page=<?= $page + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0">Categories</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($categories)): ?>
                            <li class="list-group-item">No categories found</li>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?= SITE_URL ?>/blog.php?category=<?= $category['slug'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php 
                                        $count = $blog->db->selectOne(
                                            'SELECT COUNT(*) as count FROM blog_post_categories WHERE category_id = ?', 
                                            [$category['id']]
                                        )['count']; 
                                        echo $count;
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0">Subscribe to Updates</h3>
                </div>
                <div class="card-body">
                    <p>Stay updated with our latest blog posts and news.</p>
                    <form method="POST" action="<?= SITE_URL ?>/subscribe.php">
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 