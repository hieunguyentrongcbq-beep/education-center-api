<?php
namespace App\Models;

use Core\Database;

class AuditLog {
    public static function write($userId, $action, $entityName, $entityId = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity_name, entity_id) VALUES (:uid, :act, :ent, :eid)");
        $stmt->execute([
            'uid' => $userId,
            'act' => $action,
            'ent' => $entityName,
            'eid' => $entityId,
        ]);
    }
}
