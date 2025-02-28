<?php
class AdminStats {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function recordPageView($isUniqueVisitor = false) {
        $today = date('Y-m-d');
        
        // Check if we have a record for today
        $todayStats = $this->db->selectOne('SELECT * FROM admin_statistics WHERE date = ?', [$today]);
        
        if ($todayStats) {
            // Update existing record
            $updates = ['page_views' => $todayStats['page_views'] + 1];
            
            if ($isUniqueVisitor) {
                $updates['unique_visitors'] = $todayStats['unique_visitors'] + 1;
            }
            
            $this->db->update('admin_statistics', $updates, ['id' => $todayStats['id']]);
        } else {
            // Create new record for today
            $this->db->insert('admin_statistics', [
                'date' => $today,
                'page_views' => 1,
                'unique_visitors' => $isUniqueVisitor ? 1 : 0,
                'registered_users' => $this->countUsersRegisteredToday(),
                'premium_users' => $this->countPremiumUsers()
            ]);
        }
    }
    
    public function updateRevenue($amount) {
        $today = date('Y-m-d');
        
        $todayStats = $this->db->selectOne('SELECT * FROM admin_statistics WHERE date = ?', [$today]);
        
        if ($todayStats) {
            $this->db->update('admin_statistics', [
                'total_revenue' => $todayStats['total_revenue'] + $amount
            ], ['id' => $todayStats['id']]);
        } else {
            $this->db->insert('admin_statistics', [
                'date' => $today,
                'total_revenue' => $amount,
                'registered_users' => $this->countUsersRegisteredToday(),
                'premium_users' => $this->countPremiumUsers()
            ]);
        }
    }
    
    public function getDailyStats($startDate, $endDate) {
        return $this->db->select(
            'SELECT * FROM admin_statistics WHERE date BETWEEN ? AND ? ORDER BY date',
            [$startDate, $endDate]
        );
    }
    
    public function getSummaryStats() {
        $stats = [
            'total_users' => 0,
            'premium_users' => 0,
            'recent_posts' => 0,
            'total_recipes' => 0,
            'total_revenue' => 0
        ];

        // Get user counts
        $usersResult = $this->db->selectOne("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN account_status = 'premium' THEN 1 ELSE 0 END) as premium
            FROM users");
        
        if ($usersResult) {
            $stats['total_users'] = $usersResult['total'];
            $stats['premium_users'] = $usersResult['premium'];
        }

        // Get blog post count
        try {
            $postsResult = $this->db->selectOne("SELECT COUNT(*) as total FROM blog_posts");
            if ($postsResult) {
                $stats['recent_posts'] = $postsResult['total'];
            }
        } catch (Exception $e) {
            // Table might not exist yet
            $stats['recent_posts'] = 0;
        }

        // Get recipe count - wrapped in try/catch in case table doesn't exist
        try {
            // Check if table exists first
            $tables = $this->db->select("SHOW TABLES LIKE 'recipes'");
            if (count($tables) > 0) {
                $recipesResult = $this->db->selectOne("SELECT COUNT(*) as total FROM recipes");
                if ($recipesResult) {
                    $stats['total_recipes'] = $recipesResult['total'];
                }
            }
        } catch (Exception $e) {
            // Table doesn't exist yet
            $stats['total_recipes'] = 0;
        }

        // Calculate revenue
        try {
            $revenueResult = $this->db->selectOne("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
            if ($revenueResult) {
                $stats['total_revenue'] = $revenueResult['total'] ?: 0;
            }
        } catch (Exception $e) {
            // Table might not exist yet
            $stats['total_revenue'] = 0;
        }

        return $stats;
    }
    
    public function logAdminAction($adminId, $action, $entityType = null, $entityId = null, $details = null) {
        $data = [
            'admin_id' => $adminId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details
        ];

        return $this->db->insert('admin_logs', $data);
    }
    
    public function getAdminLogs($limit = 10, $offset = 0) {
        $sql = "SELECT al.*, u.username 
                FROM admin_logs al 
                JOIN users u ON al.admin_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT ?, ?";
        return $this->db->select($sql, [$offset, $limit]);
    }
    
    private function countUsersRegisteredToday() {
        $today = date('Y-m-d');
        $result = $this->db->selectOne(
            'SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?',
            [$today]
        );
        return $result['count'];
    }
    
    private function countPremiumUsers() {
        $result = $this->db->selectOne(
            'SELECT COUNT(*) as count FROM users WHERE account_status = "premium"'
        );
        return $result['count'];
    }

    public function recordDailyStats($date, $pageViews, $uniqueVisitors, $registeredUsers, $premiumUsers, $revenue, $notes = null) {
        // Check if record for this date exists
        $existingRecord = $this->db->selectOne("SELECT id FROM admin_stats WHERE date = ?", [$date]);
        
        $data = [
            'page_views' => $pageViews,
            'unique_visitors' => $uniqueVisitors,
            'registered_users' => $registeredUsers,
            'premium_users' => $premiumUsers,
            'total_revenue' => $revenue,
            'notes' => $notes
        ];
        
        if ($existingRecord) {
            return $this->db->update('admin_stats', $data, ['id' => $existingRecord['id']]);
        } else {
            $data['date'] = $date;
            return $this->db->insert('admin_stats', $data);
        }
    }

    /**
     * Получить статистику за указанный период
     * 
     * @param string $start_date Начальная дата в формате Y-m-d
     * @param string $end_date Конечная дата в формате Y-m-d
     * @return array Массив статистических данных
     */
    public function getStatsForPeriod($start_date, $end_date) {
        $sql = "SELECT * FROM admin_stats WHERE date BETWEEN ? AND ? ORDER BY date DESC";
        return $this->db->select($sql, [$start_date, $end_date]);
    }

    /**
     * Сгенерировать ежедневную сводную статистику
     * 
     * @return bool Успешно ли сгенерирована статистика
     */
    public function generateDailyStats() {
        $today = date('Y-m-d');
        
        // Проверяем, есть ли уже статистика за сегодня
        $existingStats = $this->db->selectOne("SELECT id FROM admin_stats WHERE date = ?", [$today]);
        if ($existingStats) {
            // Уже есть статистика за сегодня, не нужно создавать новую
            return true;
        }
        
        // Получаем количество просмотров страниц (пример)
        $pageViews = $this->db->selectOne("SELECT SUM(page_views) as total FROM page_views WHERE DATE(view_date) = ?", [$today]);
        $pageViewsCount = $pageViews ? $pageViews['total'] : 0;
        
        // Получаем количество уникальных посетителей (пример)
        $uniqueVisitors = $this->db->selectOne("SELECT COUNT(DISTINCT ip_address) as total FROM page_views WHERE DATE(view_date) = ?", [$today]);
        $uniqueVisitorsCount = $uniqueVisitors ? $uniqueVisitors['total'] : 0;
        
        // Получаем количество зарегистрированных пользователей
        $registeredUsers = $this->db->selectOne("SELECT COUNT(*) as total FROM users");
        $registeredUsersCount = $registeredUsers ? $registeredUsers['total'] : 0;
        
        // Получаем количество премиум пользователей
        $premiumUsers = $this->db->selectOne("SELECT COUNT(*) as total FROM users WHERE account_status = 'premium'");
        $premiumUsersCount = $premiumUsers ? $premiumUsers['total'] : 0;
        
        // Получаем общую выручку за сегодня
        $revenue = $this->db->selectOne("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = ? AND status = 'completed'", [$today]);
        $revenueTotal = $revenue ? $revenue['total'] : 0;
        
        // Записываем статистику в базу данных
        $data = [
            'date' => $today,
            'page_views' => $pageViewsCount,
            'unique_visitors' => $uniqueVisitorsCount,
            'registered_users' => $registeredUsersCount,
            'premium_users' => $premiumUsersCount,
            'total_revenue' => $revenueTotal
        ];
        
        $result = $this->db->insert('admin_stats', $data);
        return $result ? true : false;
    }

    /**
     * Получить сводную статистику за все время
     * 
     * @return array Массив с общей статистикой
     */
    public function getTotalStats() {
        $totalStats = [
            'total_page_views' => 0,
            'total_unique_visitors' => 0,
            'total_registered_users' => 0, 
            'total_premium_users' => 0,
            'total_revenue' => 0
        ];
        
        // Общее количество просмотров
        $pageViews = $this->db->selectOne("SELECT SUM(page_views) as total FROM admin_stats");
        $totalStats['total_page_views'] = $pageViews ? $pageViews['total'] : 0;
        
        // Общее количество уникальных посетителей (приблизительно)
        $uniqueVisitors = $this->db->selectOne("SELECT SUM(unique_visitors) as total FROM admin_stats");
        $totalStats['total_unique_visitors'] = $uniqueVisitors ? $uniqueVisitors['total'] : 0;
        
        // Текущее количество пользователей
        $users = $this->db->selectOne("SELECT COUNT(*) as total FROM users");
        $totalStats['total_registered_users'] = $users ? $users['total'] : 0;
        
        // Текущее количество премиум-пользователей
        $premiumUsers = $this->db->selectOne("SELECT COUNT(*) as total FROM users WHERE account_status = 'premium'");
        $totalStats['total_premium_users'] = $premiumUsers ? $premiumUsers['total'] : 0;
        
        // Общая выручка
        $revenue = $this->db->selectOne("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
        $totalStats['total_revenue'] = $revenue ? $revenue['total'] : 0;
        
        return $totalStats;
    }
} 