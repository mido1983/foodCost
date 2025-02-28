<?php
class Blog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Post Methods
    public function createPost($data) {
        // Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }
        
        // Insert post
        return $this->db->insert('blog_posts', $data);
    }

    public function updatePost($id, $data) {
        // Generate slug if not provided
        if (isset($data['title']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = $this->createSlug($data['title'], $id);
        }
        
        // Update post
        return $this->db->update('blog_posts', $data, ['id' => $id]);
    }

    public function deletePost($id) {
        return $this->db->delete('blog_posts', ['id' => $id]);
    }

    public function getPostById($id) {
        return $this->db->selectOne(
            'SELECT p.*, u.username as author_name 
            FROM blog_posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE p.id = ?', 
            [$id]
        );
    }

    public function getPostBySlug($slug) {
        return $this->db->selectOne(
            'SELECT p.*, u.username as author_name 
            FROM blog_posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE p.slug = ?', 
            [$slug]
        );
    }

    public function getAllPosts($limit = null, $offset = null, $status = null) {
        $sql = 'SELECT p.*, u.username as author_name 
                FROM blog_posts p 
                JOIN users u ON p.author_id = u.id';
        $params = [];
        
        if ($status) {
            $sql .= ' WHERE p.status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY p.created_at DESC';
        
        if ($limit !== null) {
            $sql .= ' LIMIT ?';
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= ' OFFSET ?';
                $params[] = $offset;
            }
        }
        
        return $this->db->select($sql, $params);
    }

    public function countTotalPosts($status = null) {
        $sql = 'SELECT COUNT(*) as count FROM blog_posts';
        $params = [];
        
        if ($status) {
            $sql .= ' WHERE status = ?';
            $params[] = $status;
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result['count'];
    }

    // Category Methods
    public function createCategory($data) {
        // Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name'], null, 'blog_categories');
        }
        
        // Insert category
        return $this->db->insert('blog_categories', $data);
    }

    public function updateCategory($id, $data) {
        // Generate slug if not provided
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = $this->createSlug($data['name'], $id, 'blog_categories');
        }
        
        // Update category
        return $this->db->update('blog_categories', $data, ['id' => $id]);
    }

    public function deleteCategory($id) {
        return $this->db->delete('blog_categories', ['id' => $id]);
    }

    public function getCategoryById($id) {
        return $this->db->selectOne('SELECT * FROM blog_categories WHERE id = ?', [$id]);
    }

    public function getCategoryBySlug($slug) {
        return $this->db->selectOne('SELECT * FROM blog_categories WHERE slug = ?', [$slug]);
    }

    public function getAllCategories() {
        return $this->db->select('SELECT * FROM blog_categories ORDER BY name');
    }

    // Post-Category Relationship Methods
    public function assignCategoriesToPost($postId, $categoryIds) {
        // Delete existing relationships
        $this->db->delete('blog_post_categories', ['post_id' => $postId]);
        
        // Add new relationships
        foreach ($categoryIds as $categoryId) {
            $this->db->insert('blog_post_categories', [
                'post_id' => $postId,
                'category_id' => $categoryId
            ]);
        }
        
        return true;
    }

    public function getPostCategories($postId) {
        return $this->db->select(
            'SELECT c.* FROM blog_categories c
            JOIN blog_post_categories pc ON c.id = pc.category_id
            WHERE pc.post_id = ?
            ORDER BY c.name',
            [$postId]
        );
    }

    public function getPostCategoryIds($postId) {
        $categories = $this->getPostCategories($postId);
        return array_column($categories, 'id');
    }

    public function getPostsByCategory($categoryId, $limit = null, $offset = null, $status = 'published') {
        $sql = 'SELECT p.*, u.username as author_name 
                FROM blog_posts p 
                JOIN users u ON p.author_id = u.id
                JOIN blog_post_categories pc ON p.id = pc.post_id
                WHERE pc.category_id = ?';
        $params = [$categoryId];
        
        if ($status) {
            $sql .= ' AND p.status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY p.created_at DESC';
        
        if ($limit !== null) {
            $sql .= ' LIMIT ?';
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= ' OFFSET ?';
                $params[] = $offset;
            }
        }
        
        return $this->db->select($sql, $params);
    }

    // Helper Methods
    private function createSlug($title, $id = null, $table = 'blog_posts') {
        // Convert title to lowercase and replace spaces with hyphens
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $title)));
        
        // Make sure the slug is unique
        $originalSlug = $slug;
        $i = 1;
        
        while (true) {
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE slug = ?";
            $params = [$slug];
            
            if ($id !== null) {
                $sql .= " AND id != ?";
                $params[] = $id;
            }
            
            $result = $this->db->selectOne($sql, $params);
            
            if ($result['count'] === 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $i;
            $i++;
        }
        
        return $slug;
    }
} 