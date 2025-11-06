<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Csrf;
use App\Core\Database;
use App\Services\PlanCalculator;
use PDO;

class PlanController
{
    public function index()
    {
        AuthMiddleware::requireAuth();
        $userId = AuthMiddleware::userId();

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM trade_plans WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->execute([':uid' => $userId]);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/plans/index.php';
    }

    public function create(array $old = [], array $errors = [])
    {
        AuthMiddleware::requireAuth();
        include __DIR__ . '/../Views/plans/create.php';
    }

    public function store()
    {
        AuthMiddleware::requireAuth();
        Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        [$data, $errors] = PlanCalculator::validate($_POST);
        // Validate a few extra fields
        if (empty($_POST['plan_date'])) {
            $errors['plan_date'] = 'Required';
        }
        if (empty($_POST['symbol'])) {
            $errors['symbol'] = 'Required';
        }
        if (empty($_POST['timeframe'])) {
            $errors['timeframe'] = 'Required';
        }

        if ($errors) {
            $old = $_POST;
            $this->create($old, $errors);
            return;
        }

        $calc = PlanCalculator::computeAll($data);

        $db = Database::getInstance()->getConnection();
        $sql = "
            INSERT INTO trade_plans
            (user_id, plan_date, symbol, sector, timeframe, strategy,
             entry_price, stop_loss, target_price, capital_allocated,
             market_bias, risk_percent,
             position_size, capital_used, rr_ratio, expected_profit, expected_loss,
             confidence_level, emotion_tag, why_trade, emotion_notes,
             check_setup_matches_playbook, check_risk_within_limit, check_no_major_news, check_trend_alignment, check_execution_ready, checklist_complete,
             status, created_at, updated_at)
            VALUES
            (:user_id, :plan_date, :symbol, :sector, :timeframe, :strategy,
             :entry_price, :stop_loss, :target_price, :capital_allocated,
             :market_bias, :risk_percent,
             :position_size, :capital_used, :rr_ratio, :expected_profit, :expected_loss,
             :confidence_level, :emotion_tag, :why_trade, :emotion_notes,
             :c1, :c2, :c3, :c4, :c5, :c_all,
             'planned', NOW(), NOW())
        ";
        $stmt = $db->prepare($sql);

        $c1 = !empty($_POST['check_setup_matches_playbook']) ? 1 : 0;
        $c2 = !empty($_POST['check_risk_within_limit']) ? 1 : 0;
        $c3 = !empty($_POST['check_no_major_news']) ? 1 : 0;
        $c4 = !empty($_POST['check_trend_alignment']) ? 1 : 0;
        $c5 = !empty($_POST['check_execution_ready']) ? 1 : 0;

        $stmt->execute([
            ':user_id'          => AuthMiddleware::userId(),
            ':plan_date'        => $_POST['plan_date'],
            ':symbol'           => $_POST['symbol'],
            ':sector'           => $_POST['sector']   ?? null,
            ':timeframe'        => $_POST['timeframe'],
            ':strategy'         => $_POST['strategy'] ?? null,

            ':entry_price'      => $data['entry_price'],
            ':stop_loss'        => $data['stop_loss'],
            ':target_price'     => $data['target_price'],
            ':capital_allocated' => $data['capital_allocated'],
            ':market_bias'      => $data['market_bias'],
            ':risk_percent'     => $data['risk_percent'],

            ':position_size'    => $calc['position_size'],
            ':capital_used'     => $calc['capital_used'],
            ':rr_ratio'         => $calc['rr_ratio'],
            ':expected_profit'  => $calc['expected_profit'],
            ':expected_loss'    => $calc['expected_loss'],

            ':confidence_level' => $_POST['confidence_level'] ?? null,
            ':emotion_tag'      => $_POST['emotion_tag']      ?? null,
            ':why_trade'        => $_POST['why_trade']        ?? null,
            ':emotion_notes'    => $_POST['emotion_notes']    ?? null,

            ':c1' => $c1,
            ':c2' => $c2,
            ':c3' => $c3,
            ':c4' => $c4,
            ':c5' => $c5,
            ':c_all' => ($c1 + $c2 + $c3 + $c4 + $c5 === 5) ? 1 : 0,
        ]);

        header('Location: /plans');
        exit;
    }

    public function show($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $userId = \App\Core\AuthMiddleware::userId();

        $db = \App\Core\Database::getInstance()->getConnection();

        // fetch plan
        $stmt = $db->prepare("SELECT * FROM trade_plans WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$plan) {
            http_response_code(404);
            echo "Plan not found.";
            return;
        }

        // fetch positions with remaining qty
        $posStmt = $db->prepare("
        SELECT 
            p.*,
            (p.quantity - IFNULL(SUM(e.quantity), 0)) AS remaining_qty
        FROM trade_positions p
        LEFT JOIN trade_exits e ON e.position_id = p.id
        WHERE p.plan_id = :pid
        GROUP BY p.id
        ORDER BY p.leg_index ASC, p.opened_at ASC
    ");
        $posStmt->execute([':pid' => $plan['id']]);
        $positions = $posStmt->fetchAll(\PDO::FETCH_ASSOC);
        // fetch events (oldest → newest)
        $evtStmt = $db->prepare("
  SELECT e.*, u.name AS user_name, u.email AS user_email
  FROM trade_events e
  LEFT JOIN users u ON u.id = e.created_by
  WHERE e.trade_plan_id = :pid
  ORDER BY e.created_at ASC, e.id ASC
");
        $evtStmt->execute([':pid' => $plan['id']]);
        $events = $evtStmt->fetchAll(\PDO::FETCH_ASSOC);


        include __DIR__ . '/../Views/plans/show.php';
    }

    // helper: fetch plan (owned by user) or 404
    private function getPlanOr404($id)
    {
        $userId = \App\Core\AuthMiddleware::userId();
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM trade_plans WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$plan) {
            http_response_code(404);
            echo "Plan not found.";
            exit;
        }
        return $plan;
    }

    /* ------------ VALIDATE ------------ */
    public function validateForm($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $plan = $this->getPlanOr404($id);
        include __DIR__ . '/../Views/plans/actions/validate.php';
    }

    public function validateSubmit($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $plan = $this->getPlanOr404($id);

        // Optional: enforce checklist complete
        $reason = trim($_POST['reason'] ?? 'Validated');
        $confidence = isset($_POST['confidence_level']) ? (int)$_POST['confidence_level'] : null;
        $emotion    = $_POST['emotion_tag'] ?? null;

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE trade_plans SET status = 'validated', updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $plan['id']]);

        \App\Services\EventLogger::log('validated', (int)$plan['id'], [
            'reason' => $reason,
            'confidence' => $confidence,
            'emotion' => $emotion,
            'meta' => ['checklist_complete' => (int)$plan['checklist_complete']]
        ]);

        header('Location: /plans/' . $plan['id']);
        exit;
    }

    /* ------------ EXECUTE ------------ */
    public function executeForm($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $plan = $this->getPlanOr404($id);
        include __DIR__ . '/../Views/plans/actions/execute.php';
    }

    public function executeSubmit($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $plan = $this->getPlanOr404($id);

        $reason = trim($_POST['reason'] ?? 'Execute trade');
        $confidence = isset($_POST['confidence_level']) ? (int)$_POST['confidence_level'] : null;
        $emotion    = $_POST['emotion_tag'] ?? null;

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE trade_plans SET status = 'executed', executed_at = IFNULL(executed_at, NOW()), updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $plan['id']]);

        \App\Services\EventLogger::log('executed', (int)$plan['id'], [
            'reason' => $reason,
            'confidence' => $confidence,
            'emotion' => $emotion
        ]);

        header('Location: /plans/' . $plan['id']);
        exit;
    }

    /* ------------ CANCEL ------------ */
    public function cancelForm($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $plan = $this->getPlanOr404($id);
        $error = null;
        include __DIR__ . '/../Views/plans/actions/cancel.php';
    }

    public function cancelSubmit($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $plan = $this->getPlanOr404($id);

        $cancelReason = trim($_POST['cancel_reason'] ?? '');
        if ($cancelReason === '') {
            $error = "Cancel reason is required.";
            $plan  = $plan; // keep var for view
            include __DIR__ . '/../Views/plans/actions/cancel.php';
            return;
        }
        $confidence = isset($_POST['confidence_level']) ? (int)$_POST['confidence_level'] : null;
        $emotion    = $_POST['emotion_tag'] ?? null;

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE trade_plans
        SET status = 'canceled', canceled_at = NOW(), cancel_reason = :r, updated_at = NOW()
        WHERE id = :id");
        $stmt->execute([':r' => $cancelReason, ':id' => $plan['id']]);

        \App\Services\EventLogger::log('canceled', (int)$plan['id'], [
            'reason' => $cancelReason,
            'confidence' => $confidence,
            'emotion' => $emotion
        ]);

        header('Location: /plans/' . $plan['id']);
        exit;
    }

    /* ------------ ADJUST ------------ */
    public function adjustForm($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $plan = $this->getPlanOr404($id);
        $errors = [];
        include __DIR__ . '/../Views/plans/actions/adjust.php';
    }

    public function adjustSubmit($id)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $plan = $this->getPlanOr404($id);

        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            $errors['reason'] = 'Adjustment reason is required.';
            include __DIR__ . '/../Views/plans/actions/adjust.php';
            return;
        }

        // Only allow adjusting key risk fields for now
        $payload = [
            'entry_price'       => $_POST['entry_price'] ?? $plan['entry_price'],
            'stop_loss'         => $_POST['stop_loss'] ?? $plan['stop_loss'],
            'target_price'      => $_POST['target_price'] ?? $plan['target_price'],
            'capital_allocated' => $_POST['capital_allocated'] ?? $plan['capital_allocated'],
            'market_bias'       => $_POST['market_bias'] ?? $plan['market_bias'],
            'risk_percent'      => $_POST['risk_percent'] ?? $plan['risk_percent'],
            'side'              => $_POST['side'] ?? null, // optional
        ];

        // Validate & compute with same helper
        [$data, $vErrors] = \App\Services\PlanCalculator::validate($payload);
        if ($vErrors) {
            $errors = array_merge(['reason' => null], $vErrors);
            include __DIR__ . '/../Views/plans/actions/adjust.php';
            return;
        }
        $calc = \App\Services\PlanCalculator::computeAll($data);

        // Diff (old vs new) for logging
        $diff = [];
        foreach (['entry_price', 'stop_loss', 'target_price', 'capital_allocated', 'market_bias', 'risk_percent'] as $k) {
            if ((string)$plan[$k] !== (string)$data[$k]) $diff[$k] = ['from' => $plan[$k], 'to' => $data[$k]];
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("
        UPDATE trade_plans SET
          entry_price = :entry_price, stop_loss = :stop_loss, target_price = :target_price,
          capital_allocated = :capital_allocated, market_bias = :market_bias, risk_percent = :risk_percent,
          position_size = :position_size, capital_used = :capital_used, rr_ratio = :rr_ratio,
          expected_profit = :expected_profit, expected_loss = :expected_loss,
          last_adjusted_at = NOW(), last_adjust_reason = :reason,
          updated_at = NOW()
        WHERE id = :id
    ");
        $stmt->execute([
            ':entry_price'       => $data['entry_price'],
            ':stop_loss'         => $data['stop_loss'],
            ':target_price'      => $data['target_price'],
            ':capital_allocated' => $data['capital_allocated'],
            ':market_bias'       => $data['market_bias'],
            ':risk_percent'      => $data['risk_percent'],
            ':position_size'     => $calc['position_size'],
            ':capital_used'      => $calc['capital_used'],
            ':rr_ratio'          => $calc['rr_ratio'],
            ':expected_profit'   => $calc['expected_profit'],
            ':expected_loss'     => $calc['expected_loss'],
            ':reason'            => $reason,
            ':id'                => $plan['id'],
        ]);

        \App\Services\EventLogger::log('adjusted', (int)$plan['id'], [
            'reason' => $reason,
            'meta'   => ['diff' => $diff]
        ]);

        header('Location: /plans/' . $plan['id']);
        exit;
    }
    /* ===================== POSITIONS ===================== */

    public function positionForm($planId)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $plan = $this->getPlanOr404($planId);
        include __DIR__ . '/../Views/plans/positions/create.php';
    }

    public function positionStore($planId)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $plan = $this->getPlanOr404($planId);

        $side  = $_POST['side'] ?? 'long';
        $entry = (float)($_POST['entry_price'] ?? 0);
        $stop  = ($_POST['stop_price'] ?? '') === '' ? null : (float)$_POST['stop_price'];
        $qty   = (float)($_POST['quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (!in_array($side, ['long', 'short'], true) || $entry <= 0 || $qty <= 0) {
            $error = "Invalid side, entry or quantity.";
            include __DIR__ . '/../Views/plans/positions/create.php';
            return;
        }

        $db = \App\Core\Database::getInstance()->getConnection();

        // next leg index
        $idxStmt = $db->prepare("SELECT IFNULL(MAX(leg_index),0)+1 AS next_idx FROM trade_positions WHERE plan_id = :pid");
        $idxStmt->execute([':pid' => $plan['id']]);
        $nextIdx = (int)$idxStmt->fetch(\PDO::FETCH_ASSOC)['next_idx'];

        $stmt = $db->prepare("
        INSERT INTO trade_positions (plan_id, leg_index, side, entry_price, stop_price, quantity, status, opened_at, notes)
        VALUES (:plan_id, :leg_index, :side, :entry, :stop, :qty, 'opened', NOW(), :notes)
    ");
        $stmt->execute([
            ':plan_id'  => $plan['id'],
            ':leg_index' => $nextIdx,
            ':side'     => $side,
            ':entry'    => $entry,
            ':stop'     => $stop,
            ':qty'      => $qty,
            ':notes'    => $notes ?: null,
        ]);

        $posId = (int)$db->lastInsertId();

        // set plan executed/ongoing if not already
        if (!in_array($plan['status'], ['executed', 'ongoing'], true)) {
            $upd = $db->prepare("UPDATE trade_plans SET status = 'executed', executed_at = IFNULL(executed_at, NOW()), updated_at = NOW() WHERE id = :id");
            $upd->execute([':id' => $plan['id']]);
        } else {
            // keep it marked ongoing
            $db->prepare("UPDATE trade_plans SET status = 'ongoing', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $plan['id']]);
        }

        \App\Services\EventLogger::log('position_opened', (int)$plan['id'], [
            'position_id' => $posId,
            'reason' => $_POST['reason'] ?? 'open leg',
            'meta'   => ['side' => $side, 'entry' => $entry, 'qty' => $qty, 'stop' => $stop]
        ]);

        header('Location: /plans/' . $plan['id']);
        exit;
    }

    /* ===================== EXITS ===================== */

    private function getPositionOr404($posId, $mustBelongToUser = true)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT p.*, t.user_id, t.symbol, t.status AS plan_status
                          FROM trade_positions p
                          JOIN trade_plans t ON t.id = p.plan_id
                          WHERE p.id = :pid LIMIT 1");
        $stmt->execute([':pid' => $posId]);
        $pos = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$pos) {
            http_response_code(404);
            echo "Position not found.";
            exit;
        }

        if ($mustBelongToUser && $pos['user_id'] !== \App\Core\AuthMiddleware::userId()) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }
        return $pos;
    }

    public function exitForm($posId)
    {
        \App\Core\AuthMiddleware::requireAuth();
        $position = $this->getPositionOr404($posId);
        include __DIR__ . '/../Views/plans/exits/create.php';
    }

    public function exitStore($posId)
    {
        \App\Core\AuthMiddleware::requireAuth();
        \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $position = $this->getPositionOr404($posId);

        $price = (float)($_POST['exit_price'] ?? 0);
        $qty   = (float)($_POST['quantity'] ?? 0);
        $etype = $_POST['exit_type'] ?? 'partial';
        $reason = trim($_POST['exit_reason'] ?? '');
        $conf  = isset($_POST['confidence_level']) ? (int)$_POST['confidence_level'] : null;
        $emo   = $_POST['emotion_tag'] ?? null;

        if ($price <= 0 || $qty <= 0 || !in_array($etype, ['partial', 'full'], true)) {
            $error = "Invalid exit input.";
            $position = $position;
            include __DIR__ . '/../Views/plans/exits/create.php';
            return;
        }

        $db = \App\Core\Database::getInstance()->getConnection();

        // Insert exit
        $stmt = $db->prepare("
        INSERT INTO trade_exits (position_id, exit_price, quantity, exit_type, exit_reason, confidence_level, emotion_tag, exit_at)
        VALUES (:pid, :price, :qty, :etype, :reason, :conf, :emo, NOW())
    ");
        $stmt->execute([
            ':pid'   => $position['id'],
            ':price' => $price,
            ':qty'   => $qty,
            ':etype' => $etype,
            ':reason' => $reason ?: null,
            ':conf'  => $conf,
            ':emo'   => $emo
        ]);
        $exitId = (int)$db->lastInsertId();

        // Compute remaining qty for the position
        $remStmt = $db->prepare("
      SELECT (p.quantity - IFNULL(SUM(e.quantity),0)) AS remaining
      FROM trade_positions p
      LEFT JOIN trade_exits e ON e.position_id = p.id
      WHERE p.id = :pid
      GROUP BY p.id
    ");
        $remStmt->execute([':pid' => $position['id']]);
        $remaining = (float)$remStmt->fetch(\PDO::FETCH_ASSOC)['remaining'];

        // If remaining <= 0, mark position closed
        if ($remaining <= 1e-9) {
            $db->prepare("UPDATE trade_positions SET status='closed', closed_at=NOW() WHERE id=:id")
                ->execute([':id' => $position['id']]);
        }

        // Log event
        \App\Services\EventLogger::log($etype === 'full' ? 'exit_full' : 'exit_partial', (int)$position['plan_id'], [
            'position_id' => (int)$position['id'],
            'reason'      => $reason ?: ($etype === 'full' ? 'full exit' : 'partial exit'),
            'confidence'  => $conf,
            'emotion'     => $emo,
            'meta'        => ['exit_price' => $price, 'qty' => $qty, 'remaining' => $remaining]
        ]);

        // If ALL positions for the plan are closed, mark plan completed
        $allClosed = $db->prepare("
        SELECT COUNT(*) AS open_count
        FROM trade_positions
        WHERE plan_id = :pid AND status <> 'closed'
    ");
        $allClosed->execute([':pid' => $position['plan_id']]);
        $openCount = (int)$allClosed->fetch(\PDO::FETCH_ASSOC)['open_count'];

        if ($openCount === 0) {
            $db->prepare("UPDATE trade_plans SET status='completed', completed_at=NOW(), updated_at=NOW() WHERE id=:id")
                ->execute([':id' => $position['plan_id']]);

            \App\Services\EventLogger::log('completed', (int)$position['plan_id'], [
                'reason' => 'all legs closed'
            ]);
        } else {
            // ensure plan is marked ongoing if still has open legs
            $db->prepare("UPDATE trade_plans SET status='ongoing', updated_at=NOW() WHERE id=:id")
                ->execute([':id' => $position['plan_id']]);
        }

        header('Location: /plans/' . $position['plan_id']);
        exit;
    }
    public function journalForm($id)
{
    \App\Core\AuthMiddleware::requireAuth();
    $plan = $this->getPlanOr404($id);
    $errors = [];
    include __DIR__ . '/../Views/plans/actions/journal.php';
}

public function journalSubmit($id)
{
    \App\Core\AuthMiddleware::requireAuth();
    \App\Core\Csrf::requireValidToken($_POST['csrf_token'] ?? null);

    $plan = $this->getPlanOr404($id);

    // Minimal required: outcome + notes OR lessons
    $outcome     = $_POST['outcome'] ?? '';
    $actualPL    = trim($_POST['actual_pl'] ?? '');
    $confidence  = isset($_POST['confidence_level']) ? (int)$_POST['confidence_level'] : null;
    $emotion     = $_POST['emotion_tag'] ?? null;
    $wentWell    = trim($_POST['went_well'] ?? '');
    $improve     = trim($_POST['improve_next'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $link        = trim($_POST['link'] ?? ''); // e.g. chart/screenshot URL
    $adherence   = !empty($_POST['adhered_rules']) ? 1 : 0;

    $errors = [];
    if (!in_array($outcome, ['win','loss','breakeven'], true)) {
        $errors['outcome'] = 'Select outcome.';
    }
    if ($actualPL !== '' && !is_numeric($actualPL)) {
        $errors['actual_pl'] = 'Must be a number (or leave blank).';
    }
    if (!$errors && $wentWell === '' && $improve === '' && $tags === '' && $emotion === '' ) {
        $errors['note'] = 'Add at least one reflection (what went well, improve, tags, or emotion).';
    }

    if ($errors) {
        include __DIR__ . '/../Views/plans/actions/journal.php';
        return;
    }

    // Save a journal event
    \App\Services\EventLogger::log('journal_entry', (int)$plan['id'], [
        'reason'     => 'post-trade journal',
        'confidence' => $confidence,
        'emotion'    => $emotion,
        'note'       => $wentWell, // main note slot
        'meta'       => [
            'outcome'       => $outcome,
            'actual_pl'     => ($actualPL === '' ? null : (float)$actualPL),
            'improve_next'  => $improve,
            'tags'          => $tags,
            'link'          => $link,
            'adhered_rules' => $adherence
        ]
    ]);

    // Optional: keep latest emotion on plan for quick filters
    $db = \App\Core\Database::getInstance()->getConnection();
    $upd = $db->prepare("UPDATE trade_plans SET emotion_tag = :emo, updated_at = NOW() WHERE id = :id");
    $upd->execute([':emo' => $emotion ?: $plan['emotion_tag'], ':id' => $plan['id']]);

    header('Location: /plans/'.$plan['id'].'#timeline');
    exit;
}

}
