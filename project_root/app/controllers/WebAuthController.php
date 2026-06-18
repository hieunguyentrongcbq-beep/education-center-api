<?php
namespace App\Controllers;

use Core\WebController;
use App\Models\UserModel;
use App\Middleware\SessionAuth;

class WebAuthController extends WebController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLogin() {
        $this->render('auth/login', ['title' => 'Đăng nhập'], 'auth');
    }

    public function login() {
        $this->requirePost();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], ['email' => $email, 'password' => $password]);

        if ($errors) {
            $this->setOld(['email' => $email]);
            $this->flash('error', reset($errors));
            $this->redirect('login');
        }

        $result = $this->userModel->loginWeb($email, $password);
        if (isset($result['error'])) {
            $this->setOld(['email' => $email]);
            $this->flash('error', $result['error']);
            $this->redirect('login');
        }

        $_SESSION['user'] = $result;
        $this->clearOld();
        $this->redirect(SessionAuth::roleDashboardPath($result['role']));
    }

    public function logout() {
        session_destroy();
        session_start();
        $this->flash('success', 'Đã đăng xuất');
        $this->redirect('login');
    }
}
