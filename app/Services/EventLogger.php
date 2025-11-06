<?php
namespace App\Services;

use App\Core\AuthMiddleware;
use App\Core\Database;
use PDO;

final class EventLogger
{
    public static function log(
        string $eventType,
        int $planId,
        array $opts = [] // ['position_id'=>?, 'reason'=>?, 'confidence'=>?, 'emotion'=>?, 'note'=>?, 'meta'=>array|string]
    ): void {
        $db = Database::getInstance()->getConnection();

        $positionId = $opts['position_id'] ?? null;
        $reason     = $opts['reason']      ?? null;
        $conf       = isset($opts['confidence']) ? (int)$opts['confidence'] : null;
        $emotion    = $opts['emotion']     ?? null;
        $note       = $opts['note']        ?? null;

        // meta can be array or string; store as JSON string if array
        $meta = $opts['meta'] ?? null;
        if (is_array($meta)) {
            $meta = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $userId = AuthMiddleware::userId();

        $stmt = $db->prepare("
            INSERT INTO trade_events
                (trade_plan_id, position_id, event_type, reason, confidence_level, emotion_tag, note, meta, created_by, created_at)
            VALUES
                (:plan_id, :pos_id, :type, :reason, :conf, :emotion, :note, :meta, :by, NOW())
        ");
        $stmt->execute([
            ':plan_id' => $planId,
            ':pos_id'  => $positionId,
            ':type'    => $eventType,
            ':reason'  => $reason,
            ':conf'    => $conf,
            ':emotion' => $emotion,
            ':note'    => $note,
            ':meta'    => $meta,
            ':by'      => $userId,
        ]);
    }
}
