<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        $body = $this->getJsonBody();
        if (!isset($body['email']) || !isset($body['password'])) {
            $this->json(['error' => 'Email and password are required'], 400);
        }

        $result = $this->userModel->login($body['email'], $body['password']);

        if (isset($result['error'])) {
            $this->json(['error' => $result['error']], 401);
        }

        $this->json([
            'message' => 'Login successful',
            'data' => $result
        ]);
    }
}
