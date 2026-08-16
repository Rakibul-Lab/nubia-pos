<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

/**
 * Current user's profile & password.
 *
 * @package App\Controllers
 */
final class ProfileController extends Controller
{
    public function edit(): void
    {
        $this->view('profile.index', ['title' => 'My Profile', 'user' => Auth::user()]);
    }

    public function update(): void
    {
        $this->verifyCsrf();
        $data = $this->validate([
            'name'  => 'required|min:2|max:120',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);
        (new User())->update((int) Auth::id(), [
            'name'  => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'phone' => $this->request->string('phone') ?: null,
        ]);
        flash('success', 'Profile updated.');
        redirect('profile');
    }

    public function changePassword(): void
    {
        $this->verifyCsrf();
        $data = $this->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = Database::getInstance()->fetch('SELECT password FROM users WHERE id = ?', [Auth::id()]);
        if (!$user || !password_verify((string) $data['current_password'], $user['password'])) {
            flash('error', 'Current password is incorrect.');
            redirect('profile');
        }

        (new User())->update((int) Auth::id(), [
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
        ]);
        flash('success', 'Password changed successfully.');
        redirect('profile');
    }
}
