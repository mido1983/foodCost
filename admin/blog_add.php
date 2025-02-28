<?php
require_once '../includes/config.php';

$page_title = 'Add New Post';
$current_page = 'admin_blog';

// Create blog instance
$blog = new Blog();

// Get categories for dropdown
$categories = $blog->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $status = $_POST['status'];
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required';
    }
    
    // Upload featured image if provided
    $featured_image = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/blog/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $temp_name = $_FILES['featured_image']['tmp_name'];
        $image_name = time() . '_' . $_FILES['featured_image']['name'];
        $upload_path = $upload_dir . $image_name;
        
        // Check if file is an image
        $image_info = getimagesize($temp_name);
        if ($image_info === false) {
            $errors[] = 'Uploaded file is not a valid image';
        } else {
            // Move the uploaded file
            if (move_uploaded_file($temp_name, $upload_path)) {
                $featured_image = $image_name;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }
    
    if (empty($errors)) {
        // Insert post
        $post_data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => !empty($excerpt) ? $excerpt : null,
            'author_id' => Session::get('user_id'),
            'status' => $status,
            'featured_image' => $featured_image
        ];
        
        $post_id = $blog->createPost($post_data);
        
        if ($post_id) {
            // Assign categories
            if (!empty($category_ids)) {
                $blog->assignCategoriesToPost($post_id, $category_ids);
            }
            
            // Log admin action
            logAdminAction('create_post', 'post', $post_id);
            
            Session::setFlash('success', 'Post created successfully');
            header('Location: ' . SITE_URL . '/admin/blog.php');
            exit;
        } else {
            $errors[] = 'Failed to create post';
        }
    }
}

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Post</h1>
    <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to All Posts
    </a>
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
                <input type="text" id="title" name="title" class="form-control" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-control" rows="12" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="excerpt" class="form-label">Excerpt (optional)</label>
                <textarea id="excerpt" name="excerpt" class="form-control" rows="3"><?= isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : '' ?></textarea>
                <small class="form-text text-muted">A short summary of the post that will be displayed on the blog page.</small>
            </div>
            
            <div class="mb-3">
                <label for="featured_image" class="form-label">Featured Image (optional)</label>
                <input type="file" id="featured_image" name="featured_image" class="form-control">
            </div>
            
            <div class="mb-3">
                <label for="categories" class="form-label">Categories</label>
                <select id="categories" name="categories[]" class="form-control" multiple>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= isset($_POST['categories']) && in_array($category['id'], $_POST['categories']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple categories.</small>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="draft" <?= isset($_POST['status']) && $_POST['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= isset($_POST['status']) && $_POST['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= isset($_POST['status']) && $_POST['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Post</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 