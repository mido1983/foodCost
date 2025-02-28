<?php
require_once '../includes/config.php';

$page_title = 'Edit Post';
$current_page = 'admin_blog';

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

// Get categories for dropdown
$categories = $blog->getAllCategories();
$post_category_ids = $blog->getPostCategoryIds($post_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $status = $_POST['status'];
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Generate slug if title has changed
    $slug = $post['slug'];
    if ($title !== $post['title']) {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
    }
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required';
    }
    
    // Upload featured image if provided
    $featured_image = $post['featured_image'];
    if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) {
        try {
            $uploader = new FileUploader('blog');
            $fileName = $uploader->setFile($_FILES['featured_image'])->upload();
            
            // Delete old image if exists
            if ($featured_image && file_exists('../uploads/blog/' . $featured_image)) {
                unlink('../uploads/blog/' . $featured_image);
            }
            $featured_image = $fileName;
        } catch (Exception $e) {
            $errors[] = 'Image upload error: ' . $e->getMessage();
        }
    }
    
    // Remove featured image if requested
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($featured_image && file_exists('../uploads/blog/' . $featured_image)) {
            unlink('../uploads/blog/' . $featured_image);
        }
        $featured_image = null;
    }
    
    if (empty($errors)) {
        // Update post
        $post_data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => !empty($excerpt) ? $excerpt : null,
            'status' => $status,
            'featured_image' => $featured_image
        ];
        
        if ($blog->updatePost($post_id, $post_data)) {
            // Assign categories
            $blog->assignCategoriesToPost($post_id, $category_ids);
            
            // Log admin action
            logAdminAction('update_post', 'post', $post_id);
            
            Session::setFlash('success', 'Post updated successfully');
            header('Location: ' . SITE_URL . '/admin/blog.php');
            exit;
        } else {
            $errors[] = 'Failed to update post';
        }
    }
}

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Post</h1>
    <div>
        <a href="<?= SITE_URL ?>/post.php?slug=<?= $post['slug'] ?>" class="btn btn-sm btn-info" target="_blank">
            <i class="fas fa-eye"></i> View Post
        </a>
        <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Posts
        </a>
    </div>
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
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-control" rows="12" required><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="excerpt" class="form-label">Excerpt (optional)</label>
                <textarea id="excerpt" name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                <small class="form-text text-muted">A short summary of the post that will be displayed on the blog page.</small>
            </div>
            
            <div class="mb-3">
                <label for="featured_image" class="form-label">Featured Image</label>
                <?php if ($post['featured_image']): ?>
                <div class="mb-2">
                    <img src="<?= SITE_URL . '/' . $post['featured_image'] ?>" alt="Featured Image" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                        <label class="form-check-label" for="remove_image">Remove current image</label>
                    </div>
                </div>
                <?php endif; ?>
                <input type="file" id="featured_image" name="featured_image" class="form-control">
                <small class="form-text text-muted">Upload a new image to replace the current one.</small>
            </div>
            
            <div class="mb-3">
                <label for="categories" class="form-label">Categories</label>
                <select id="categories" name="categories[]" class="form-control" multiple>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= in_array($category['id'], $post_category_ids) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple categories.</small>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 