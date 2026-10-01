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
        $email = trim((string)$this->request->getPost('email'));
        $password = (string)$this->request->getPost('password');

        // Security Layer: Brute-Force Rate Limiting (5 attempts per minute per IP)
        $throttler = \Config\Services::throttler();
        $ip = $this->request->getIPAddress();
        $throttleKey = 'login_attempt_' . md5($ip);

        if ($throttler->check($throttleKey, 5, MINUTE) === false) {
            $remaining = $throttler->getTokenTime();
            return redirect()->back()->with('error', "Security Alert: Too many login attempts from this network. Please wait {$remaining} seconds before attempting to sign in again.");
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account has been deactivated. Please contact the system administrator.');
            }

            // Security Layer: Prevent Session Fixation attacks
            $session->regenerate(true);

            $sessionData = [
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_email'=> $user['email'],
                'user_role' => $user['role'],
                'logged_in' => true,
            ];
            $session->set($sessionData);

            if ($user['role'] === 'driver') {
                return redirect()->to('/driver/trips')->with('success', 'Welcome, ' . esc($user['name']) . '! Your active assignments are ready.');
            }
            return redirect()->to('/')->with('success', 'Welcome back, ' . esc($user['name']) . '! Security credentials verified.');
        }

        return redirect()->back()->with('error', 'Invalid email or password. Please verify your credentials.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been successfully logged out.');
    }
}
