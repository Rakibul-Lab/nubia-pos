<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;

/**
 * Handles authentication: login, logout, password reset.
 *
 * @package App\Controllers
 */
final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->bare('auth.login', ['title' => 'Sign In']);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $data = $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email    = strtolower(trim((string) $data['email']));
        $remember = $this->request->bool('remember');

        if (!Auth::attempt($email, (string) $data['password'], $remember)) {
            ActivityLog::record('login.failed', 'Auth', 'Failed login for ' . $email);
            flash('error', 'Invalid email or password.');
            \App\Core\Session::flashOld(['email' => $email]);
            redirect('login');
        }

        ActivityLog::record('login', 'Auth', 'User signed in');
        flash('success', 'Welcome back, ' . (auth()['name'] ?? '') . '!');
        redirect(Auth::landingPath());
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        ActivityLog::record('logout', 'Auth', 'User signed out');
        Auth::logout();
        flash('success', 'You have been signed out.');
        redirect('login');
    }

    public function showForgot(): void
    {
        $this->bare('auth.forgot', ['title' => 'Forgot Password']);
    }

    public function sendReset(): void
    {
        $this->verifyCsrf();
        $data = $this->validate(['email' => 'required|email']);
        $email = strtolower(trim((string) $data['email']));

        $db   = Database::getInstance();
        $user = $db->fetch('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);

        // Always show success to prevent user enumeration.
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db->query('DELETE FROM password_resets WHERE email = ?', [$email]);
            $db->insert('password_resets', [
                'email'      => $email,
                'token'      => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => now(),
            ]);

            // In production this link is emailed via PHPMailer.
            $resetLink = url('reset-password/' . $token . '?email=' . urlencode($email));
            \App\Services\Mailer::send(
                $email,
                'Reset your Nubia Inventory password',
                "Click the link to reset your password:\n{$resetLink}\n\nThis link expires in 1 hour."
            );
            ActivityLog::record('password.reset_requested', 'Auth', 'Reset requested for ' . $email);
        }

        $this->bare('auth.forgot', [
            'title'  => 'Forgot Password',
            'status' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    public function showReset(string $token): void
    {
        $this->bare('auth.reset', [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $this->request->query('email', ''),
        ]);
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();
        $data = $this->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $db  = Database::getInstance();
        $row = $db->fetch(
            'SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() LIMIT 1',
            [strtolower((string) $data['email']), hash('sha256', (string) $data['token'])]
        );

        if (!$row) {
            flash('error', 'This password reset link is invalid or has expired.');
            redirect('forgot-password');
        }

        $db->update(
            'users',
            ['password' => password_hash((string) $data['password'], PASSWORD_DEFAULT)],
            ['email' => strtolower((string) $data['email'])]
        );
        $db->query('DELETE FROM password_resets WHERE email = ?', [strtolower((string) $data['email'])]);

        ActivityLog::record('password.reset', 'Auth', 'Password reset completed');
        flash('success', 'Password updated. Please sign in.');
        redirect('login');
    }
}
