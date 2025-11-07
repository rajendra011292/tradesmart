<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use App\Core\View;
use PDO;

class DashboardController
{
    public function index()
    {
        AuthMiddleware::requireAuth();
        $userId = AuthMiddleware::userId();
        $db = Database::getInstance()->getConnection();

        // initialize defaults to avoid "undefined variable" warnings
        $statusCounts = [];
        $totPlans = $openPlans = $openPositions = $completed30 = 0;
        $recentPlans = $recentEvents = [];

        // --- Totals by status ---
        $stmt = $db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM trade_plans
            WHERE user_id = :uid
            GROUP BY status
        ");
        $stmt->execute([':uid' => $userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $statusCounts[$row['status']] = (int)$row['cnt'];
        }

        $totPlans = array_sum($statusCounts);
        $openPlans = ($statusCounts['planned'] ?? 0)
            + ($statusCounts['validated'] ?? 0)
            + ($statusCounts['executed'] ?? 0)
            + ($statusCounts['ongoing'] ?? 0);

        // --- Completed last 30 days ---
        $stmt = $db->prepare("
            SELECT COUNT(*) AS c
            FROM trade_plans
            WHERE user_id = :uid
              AND status = 'completed'
              AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([':uid' => $userId]);
        $completed30 = (int)$stmt->fetchColumn();

        // --- Open positions count ---
        $stmt = $db->prepare("
            SELECT COUNT(*) AS c
            FROM trade_positions p
            JOIN trade_plans t ON t.id = p.plan_id
            WHERE t.user_id = :uid AND p.status <> 'closed'
        ");
        $stmt->execute([':uid' => $userId]);
        $openPositions = (int)$stmt->fetchColumn();

        // --- Recent plans (5) ---
        $stmt = $db->prepare("
            SELECT id, symbol, plan_date, status, rr_ratio
            FROM trade_plans
            WHERE user_id = :uid
            ORDER BY updated_at DESC
            LIMIT 5
        ");
        $stmt->execute([':uid' => $userId]);
        $recentPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Recent events (10) ---
        $stmt = $db->prepare("
            SELECT e.event_type, e.reason, e.created_at, e.trade_plan_id, tp.symbol
            FROM trade_events e
            JOIN trade_plans tp ON tp.id = e.trade_plan_id
            WHERE tp.user_id = :uid
            ORDER BY e.created_at DESC, e.id DESC
            LIMIT 10
        ");
        $stmt->execute([':uid' => $userId]);
        $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ===== Analytics: Status pie data =====
        $statusLabels = ['planned', 'validated', 'executed', 'ongoing', 'completed', 'canceled'];
        $statusData = [];
        foreach ($statusLabels as $lbl) {
            $statusData[] = (int)($statusCounts[$lbl] ?? 0);
        }

        // ===== Analytics: Completed per month (last 6 months) =====
        $mcStmt = $db->prepare("
    SELECT DATE_FORMAT(completed_at, '%Y-%m') AS ym, COUNT(*) AS c
    FROM trade_plans
    WHERE user_id = :uid
      AND status = 'completed'
      AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym
    ORDER BY ym ASC
");
        $mcStmt->execute([':uid' => $userId]);
        $rows = $mcStmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize last 6 months (even if zero)
        $monthLabels = [];
        $monthData   = [];
        $cursor = new \DateTime('first day of -5 month'); // includes current month end of loop
        $end    = new \DateTime('first day of next month');

        $map = [];
        foreach ($rows as $r) {
            $map[$r['ym']] = (int)$r['c'];
        }

        while ($cursor < $end) {
            $key = $cursor->format('Y-m');
            $monthLabels[] = $key;
            $monthData[]   = $map[$key] ?? 0;
            $cursor->modify('+1 month');
        }

        // ===== Analytics: Journal outcome distribution (win/loss/breakeven) =====
        // We stored journal outcome in trade_events.meta (TEXT JSON). Use LIKE filters (portable).
        $joStmt = $db->prepare("
    SELECT
      SUM(meta LIKE '%\"outcome\":\"win\"%')       AS win_cnt,
      SUM(meta LIKE '%\"outcome\":\"loss\"%')      AS loss_cnt,
      SUM(meta LIKE '%\"outcome\":\"breakeven\"%') AS be_cnt
    FROM trade_events
    WHERE event_type = 'journal_entry'
      AND trade_plan_id IN (SELECT id FROM trade_plans WHERE user_id = :uid)
");
        $joStmt->execute([':uid' => $userId]);
        $jo = $joStmt->fetch(PDO::FETCH_ASSOC) ?: ['win_cnt' => 0, 'loss_cnt' => 0, 'be_cnt' => 0];

        $outcomeLabels = ['win', 'loss', 'breakeven'];
        $outcomeData   = [(int)$jo['win_cnt'], (int)$jo['loss_cnt'], (int)$jo['be_cnt']];

        // Pass to view
        View::render('dashboard', [
            'title'         => 'Dashboard',
            'statusCounts'  => $statusCounts,
            'totPlans'      => $totPlans,
            'openPlans'     => $openPlans,
            'openPositions' => $openPositions,
            'completed30'   => $completed30,
            'recentPlans'   => $recentPlans,
            'recentEvents'  => $recentEvents,
            // analytics payloads
            'statusLabels'  => $statusLabels,
            'statusData'    => $statusData,
            'monthLabels'   => $monthLabels,
            'monthData'     => $monthData,
            'outcomeLabels' => $outcomeLabels,
            'outcomeData'   => $outcomeData,
        ]);
    }
}
