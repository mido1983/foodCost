<?php
require_once '../includes/config.php';

$page_title = 'Blog Management';
$current_page = 'admin_blog';

// Create blog instance
$blog = new Blog();

// Pagination
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($current_page_num - 1) * $limit;

// Get posts with pagination
$posts = $blog->getAllPosts($limit, $offset);
$total_posts = $blog->countTotalPosts();
$total_pages = ceil($total_posts / $limit);

// Handle post deletion
if (isset($_POST['delete']) && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    
    if ($blog->deletePost($post_id)) {
        logAdminAction('delete_post', 'post', $post_id);
        Session::setFlash('success', 'Post deleted successfully');
    } else {
        Session::setFlash('error', 'Failed to delete post');
    }
    
    // Redirect to avoid form resubmission
    header('Location: ' . SITE_URL . '/admin/blog.php');
    exit;
}

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Management</h1>
    <a href="<?= SITE_URL ?>/admin/blog_add.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Post
    </a>
</div>

<!-- Blog Posts Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">All Posts</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Blog Options:</div>
                <a class="dropdown-item" href="<?= SITE_URL ?>/admin/blog_categories.php">Manage Categories</a>
                <a class="dropdown-item" href="<?= SITE_URL ?>/blog">View Blog</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($posts)): ?>
            <p class="text-center">No blog posts found. <a href="<?= SITE_URL ?>/admin/blog_add.php">Create your first post</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/blog_edit.php?id=<?= $post['id'] ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($post['author_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/blog_edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php if ($post['status'] !== 'published'): ?>
                                <a href="<?= SITE_URL ?>/admin/blog_publish.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $current_page_num ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 