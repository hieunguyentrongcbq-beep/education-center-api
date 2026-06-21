<?php
namespace App\Controllers;

use Core\WebController;
use App\Models\classmodel;
use App\Models\instructormodel;

/**
 * JSON API cho portal PHP (session auth) — hỗ trợ AJAX CRUD (K07).
 */
class PortalApiController extends WebController {
    public function listClassrooms() {
        $this->requireMethod(['GET']);
        $model = new classmodel();
        $this->json(['data' => $model->listClassroomsAdmin()]);
    }

    public function createClassroom() {
        $this->requireMethod(['POST']);
        $model = new classmodel();
        $result = $model->createClassroom($this->inputJson());
        if (isset($result['error'])) {
            $this->json(['error' => $result['error']], 400);
        }
        $this->audit('CREATE_CLASSROOM', 'classrooms', $result['id'] ?? null);
        $this->json(['message' => 'Đã tạo phòng học', 'data' => $result], 201);
    }

    public function updateClassroom($params) {
        $this->requireMethod(['PUT', 'POST']);
        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $this->json(['error' => 'Thiếu ID'], 400);
        }
        $model = new classmodel();
        $result = $model->updateClassroom($id, $this->inputJson());
        if (isset($result['error'])) {
            $this->json(['error' => $result['error']], 400);
        }
        $this->audit('UPDATE_CLASSROOM', 'classrooms', $id);
        $this->json(['message' => 'Đã cập nhật phòng học', 'data' => ['id' => $id]]);
    }

    public function deleteClassroom($params) {
        $this->requireMethod(['DELETE', 'POST']);
        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $this->json(['error' => 'Thiếu ID'], 400);
        }
        $model = new classmodel();
        $result = $model->deleteClassroom($id);
        if (isset($result['error'])) {
            $this->json(['error' => $result['error']], 400);
        }
        $this->audit('DELETE_CLASSROOM', 'classrooms', $id);
        $this->json(['message' => 'Đã xóa phòng học']);
    }

    public function unreadNotificationCount() {
        $this->requireMethod(['GET']);
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $count = (new instructormodel())->countUnreadNotifications($userId);
        $this->json(['unread' => $count]);
    }

    public function markNotificationRead($params) {
        $this->requireMethod(['POST']);
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $this->json(['error' => 'Thiếu ID'], 400);
        }
        $model = new instructormodel();
        if (!$model->markNotificationAsRead($id, $userId)) {
            $this->json(['error' => 'Không thể đánh dấu đã đọc'], 400);
        }
        $this->json([
            'message' => 'Đã đánh dấu đã đọc',
            'unread' => $model->countUnreadNotifications($userId),
        ]);
    }

    public function markAllNotificationsRead() {
        $this->requireMethod(['POST']);
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $model = new instructormodel();
        $model->markAllNotificationsAsRead($userId);
        $this->json(['message' => 'Đã đánh dấu tất cả là đã đọc', 'unread' => 0]);
    }
}
