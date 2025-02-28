<?php
require_once '../includes/config.php';

$page_title = 'Blog Categories';
$current_page = 'admin_blog';

// Create blog instance
$blog = new Blog();

// Handle category creation
if (isset($_POST['create_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if (empty($name)) {
        Session::setFlash('error', 'Category name is required');
    } else {
        $data = [
            'name' => $name,
            'description' => !empty($description) ? $description : null
        ];
        
        $category_id = $blog->createCategory($data);
        
        if ($category_id) {
            logAdminAction('create_category', 'category', $category_id);
            Session::setFlash('success', 'Category created successfully');
            header('Location: ' . SITE_URL . '/admin/blog_categories.php');
            exit;
        } else {
            Session::setFlash('error', 'Failed to create category');
        }
    }
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    $category_id = (int)$_POST['category_id'];
    
    if ($blog->deleteCategory($category_id)) {
        logAdminAction('delete_category', 'category', $category_id);
        Session::setFlash('success', 'Category deleted successfully');
    } else {
        Session::setFlash('error', 'Failed to delete category');
    }
    
    header('Location: ' . SITE_URL . '/admin/blog_categories.php');
    exit;
}

// Handle category update
if (isset($_POST['update_category'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if (empty($name)) {
        Session::setFlash('error', 'Category name is required');
    } else {
        $data = [
            'name' => $name,
            'description' => !empty($description) ? $description : null
        ];
        
        if ($blog->updateCategory($category_id, $data)) {
            logAdminAction('update_category', 'category', $category_id);
            Session::setFlash('success', 'Category updated successfully');
            header('Location: ' . SITE_URL . '/admin/blog_categories.php');
            exit;
        } else {
            Session::setFlash('error', 'Failed to update category');
        }
    }
}

// Get all categories
$categories = $blog->getAllCategories();

require_once '../includes/admin_header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Categories</h1>
    <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Blog
    </a>
</div>

<div class="row">
    <!-- Categories List -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Categories</h6>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-center">No categories found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td><?= htmlspecialchars($category['slug']) ?></td>
                                    <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-category-btn" 
                                                data-id="<?= $category['id'] ?>" 
                                                data-name="<?= htmlspecialchars($category['name']) ?>" 
                                                data-description="<?= htmlspecialchars($category['description'] ?? '') ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create/Edit Category Form -->
    <div class="col-md-4">
        <div class="card shadow mb-4" id="category-form-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary" id="form-title">Create New Category</h6>
            </div>
            <div class="card-body">
                <form action="" method="post" id="category-form">
                    <input type="hidden" name="category_id" id="category_id" value="">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optional)</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="create_category" id="submit-btn" class="btn btn-primary">Create Category</button>
                        <button type="button" id="cancel-btn" class="btn btn-secondary" style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('category-form');
    const formTitle = document.getElementById('form-title');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const categoryIdInput = document.getElementById('category_id');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    
    // Edit category buttons
    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            
            // Update form
            categoryIdInput.value = id;
            nameInput.value = name;
            descriptionInput.value = description;
            
            // Change form appearance
            formTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Update Category';
            submitBtn.name = 'update_category';
            cancelBtn.style.display = 'block';
            
            // Scroll to form
            document.getElementById('category-form-card').scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Cancel button
    cancelBtn.addEventListener('click', function() {
        // Reset form
        form.reset();
        categoryIdInput.value = '';
        
        // Change form appearance back
        formTitle.textContent = 'Create New Category';
        submitBtn.textContent = 'Create Category';
        submitBtn.name = 'create_category';
        cancelBtn.style.display = 'none';
    });
});
</script>

<?php require_once '../includes/admin_footer.php'; ?> 