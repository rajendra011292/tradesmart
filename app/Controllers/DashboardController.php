<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use PDO;

class DashboardController
{
    public function index()
    {
        AuthMiddleware::requireAuth();
        $userId = AuthMiddleware::userId();
        $db = Database::getInstance()->getConnection();

        // Totals by status
        $statusStmt = $db->prepare("
            SELECT status, COUNT(*) cnt
            FROM trade_plans
            WHERE user_id = :uid
            GROUP BY status
        ");
        $statusStmt->execute([':uid' => $userId]);
        $statusCounts = [];
        foreach ($statusStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $statusCounts[$row['status']] = (int)$row['cnt'];
        }

        // Quick totals
        $totPlans = array_sum($statusCounts);
        $openPlans = ($statusCounts['planned'] ?? 0)
                   + ($statusCounts['validated'] ?? 0)
                   + ($statusCounts['executed'] ?? 0)
                   + ($statusCounts['ongoing'] ?? 0);

        // Completed last 30 days
        $completedStmt = $db->prepare("
            SELECT COUNT(*) AS c
            FROM trade_plans
            WHERE user_id = :uid
              AND status = 'completed'
              AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $completedStmt->execute([':uid' => $userId]);
        $completed30 = (int)$completedStmt->fetch(PDO::FETCH_ASSOC)['c'];

        // Open positions count
        $posStmt = $db->prepare("
            SELECT COUNT(*) AS c
            FROM trade_positions p
            JOIN trade_plans t ON t.id = p.plan_id
            WHERE t.user_id = :uid AND p.status <> 'closed'
        ");
        $posStmt->execute([':uid' => $userId]);
        $openPositions = (int)$posStmt->fetch(PDO::FETCH_ASSOC)['c'];

        // Recent plans (5)
        $plansStmt = $db->prepare("
            SELECT id, symbol, plan_date, status, rr_ratio
            FROM trade_plans
            WHERE user_id = :uid
            ORDER BY updated_at DESC
            LIMIT 5
        ");
        $plansStmt->execute([':uid' => $userId]);
        $recentPlans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent events (10)
        $eventsStmt = $db->prepare("
            SELECT e.event_type, e.reason, e.created_at, e.trade_plan_id, tp.symbol
            FROM trade_events e
            JOIN trade_plans tp ON tp.id = e.trade_plan_id
            WHERE tp.user_id = :uid
            ORDER BY e.created_at DESC, e.id DESC
            LIMIT 10
        ");
        $eventsStmt->execute([':uid' => $userId]);
        $recentEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/dashboard.php';
    }
}
