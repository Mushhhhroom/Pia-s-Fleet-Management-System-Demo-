<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    public function authenticate()
    {
        $session = session();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account has been deactivated. Please contact the administrator.');
            }

            $sessionData = [
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_email'=> $user['email'],
                'user_role' => $user['role'],
                'logged_in' => true,
            ];
            $session->set($sessionData);
            if ($user['role'] === 'driver') {
                return redirect()->to('/driver/trips')->with('success', 'Welcome, ' . esc($user['name']) . '!');
            }
            return redirect()->to('/')->with('success', 'Welcome back, ' . esc($user['name']) . '!');
        }

        return redirect()->back()->with('error', 'Invalid email or password.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been successfully logged out.');
    }
}
