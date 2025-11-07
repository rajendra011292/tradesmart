<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use PDO;

class ExportController
{
    private function csvHeaders(string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    /** GET /plans/export.csv?from=YYYY-MM-DD&to=YYYY-MM-DD&status=completed */
    public function plansCsv(): void
    {
        AuthMiddleware::requireAuth();
        $uid = AuthMiddleware::userId();
        $db  = Database::getInstance()->getConnection();

        // Filters
        $from   = $_GET['from']   ?? null;
        $to     = $_GET['to']     ?? null;
        $status = $_GET['status'] ?? null;

        $where = ["user_id = :uid"];
        $bind  = [':uid' => $uid];

        if ($status) {
            $where[] = "status = :status";
            $bind[':status'] = $status;
        }
        if ($from) {
            $where[] = "plan_date >= :from";
            $bind[':from'] = $from;
        }
        if ($to) {
            $where[] = "plan_date <= :to";
            $bind[':to'] = $to;
        }

        $sql = "
          SELECT
            id, plan_date, symbol, sector, timeframe, strategy,
            entry_price, stop_loss, target_price, capital_allocated,
            market_bias, risk_percent,
            position_size, capital_used, rr_ratio, expected_profit, expected_loss,
            confidence_level, emotion_tag, status, created_at, updated_at, executed_at, completed_at, canceled_at
          FROM trade_plans
          WHERE ".implode(' AND ', $where)."
          ORDER BY plan_date DESC, id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->csvHeaders('plans_export.csv');

        $out = fopen('php://output', 'w');
        // header row
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $r) fputcsv($out, $r);
        } else {
            fputcsv($out, ['No data']);
        }
        fclose($out);
        exit;
    }

    /** GET /plans/{id}/events.csv */
    public function planEventsCsv(int $planId): void
    {
        AuthMiddleware::requireAuth();
        $uid = AuthMiddleware::userId();
        $db  = Database::getInstance()->getConnection();

        // Ownership check
        $chk = $db->prepare("SELECT id, symbol FROM trade_plans WHERE id = :id AND user_id = :uid LIMIT 1");
        $chk->execute([':id'=>$planId, ':uid'=>$uid]);
        $plan = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$plan) { http_response_code(404); echo "Plan not found"; exit; }

        $stmt = $db->prepare("
          SELECT
            e.id, e.event_type, e.reason, e.confidence_level, e.emotion_tag, e.note, e.meta,
            e.position_id, e.created_by, e.created_at
          FROM trade_events e
          WHERE e.trade_plan_id = :pid
          ORDER BY e.created_at ASC, e.id ASC
        ");
        $stmt->execute([':pid' => $planId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fname = 'plan_'.$planId.'_events.csv';
        $this->csvHeaders($fname);

        $out = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $r) {
                // meta is JSON string stored as TEXT — keep as-is for portability
                fputcsv($out, $r);
            }
        } else {
            fputcsv($out, ['No events for this plan.']);
        }
        fclose($out);
        exit;
    }
    public function planPositionsCsv(int $planId): void
{
    \App\Core\AuthMiddleware::requireAuth();
    $uid = \App\Core\AuthMiddleware::userId();
    $db  = \App\Core\Database::getInstance()->getConnection();

    // ownership check
    $chk = $db->prepare("SELECT id, symbol FROM trade_plans WHERE id=:id AND user_id=:uid LIMIT 1");
    $chk->execute([':id'=>$planId, ':uid'=>$uid]);
    $plan = $chk->fetch(\PDO::FETCH_ASSOC);
    if (!$plan) { http_response_code(404); echo "Plan not found"; exit; }

    // positions + remaining qty
    $stmt = $db->prepare("
      SELECT 
        p.id, p.plan_id, p.leg_index, p.side, p.entry_price, p.stop_price,
        p.quantity AS qty_opened,
        (p.quantity - IFNULL(SUM(e.quantity),0)) AS qty_remaining,
        p.status, p.opened_at, p.closed_at, p.notes
      FROM trade_positions p
      LEFT JOIN trade_exits e ON e.position_id = p.id
      WHERE p.plan_id = :pid
      GROUP BY p.id
      ORDER BY p.leg_index ASC, p.opened_at ASC
    ");
    $stmt->execute([':pid'=>$planId]);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $this->csvHeaders('plan_'.$planId.'_positions.csv');
    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $r) fputcsv($out, $r);
    } else {
        fputcsv($out, ['No positions for this plan.']);
    }
    fclose($out);
    exit;
}

public function planExitsCsv(int $planId): void
{
    \App\Core\AuthMiddleware::requireAuth();
    $uid = \App\Core\AuthMiddleware::userId();
    $db  = \App\Core\Database::getInstance()->getConnection();

    // ownership check
    $chk = $db->prepare("SELECT id, symbol FROM trade_plans WHERE id=:id AND user_id=:uid LIMIT 1");
    $chk->execute([':id'=>$planId, ':uid'=>$uid]);
    $plan = $chk->fetch(\PDO::FETCH_ASSOC);
    if (!$plan) { http_response_code(404); echo "Plan not found"; exit; }

    // exits (with leg info)
    $stmt = $db->prepare("
      SELECT 
        e.id, p.plan_id, p.leg_index, p.side,
        e.position_id, e.exit_price, e.quantity, e.exit_type,
        e.exit_reason, e.confidence_level, e.emotion_tag, e.exit_at
      FROM trade_exits e
      JOIN trade_positions p ON p.id = e.position_id
      WHERE p.plan_id = :pid
      ORDER BY e.exit_at ASC, e.id ASC
    ");
    $stmt->execute([':pid'=>$planId]);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $this->csvHeaders('plan_'.$planId.'_exits.csv');
    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $r) fputcsv($out, $r);
    } else {
        fputcsv($out, ['No exits for this plan.']);
    }
    fclose($out);
    exit;
}

}
