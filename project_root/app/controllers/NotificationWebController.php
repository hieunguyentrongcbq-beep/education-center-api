<?php

namespace App\Controllers;



use Core\WebController;

use App\Models\instructormodel;



class NotificationWebController extends WebController {

    private $model;



    public function __construct() {

        $this->model = new instructormodel();

    }



    public function index() {

        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $notifications = $this->model->getMyNotifications($userId);

        $unreadCount = $this->model->countUnreadNotifications($userId);



        $this->render('notifications/index', [

            'title' => 'Thông báo',

            'portal' => $this->portalKey(),

            'notifications' => $notifications,

            'unreadCount' => $unreadCount,

            'pageScripts' => ['notifications-ajax.js'],

        ]);

    }



    public function markRead($params) {

        $this->requirePost();

        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $id = (int)($params['id'] ?? 0);

        if ($id && $this->model->markNotificationAsRead($id, $userId)) {

            $this->flash('success', 'Đã đánh dấu đã đọc');

        }

        $this->redirect($this->notificationsPath());

    }



    public function markAllRead() {

        $this->requirePost();

        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $this->model->markAllNotificationsAsRead($userId);

        $this->flash('success', 'Đã đánh dấu tất cả là đã đọc');

        $this->redirect($this->notificationsPath());

    }



    private function portalKey(): string {

        return strtolower($_SESSION['user']['role'] ?? 'admin');

    }



    private function notificationsPath(): string {

        return $this->portalKey() . '/notifications';

    }

}


