<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Services\NotificationService;

/**
 * Notification API for the topbar bell.
 *
 * @package App\Controllers
 */
final class NotificationController extends Controller
{
    /**
     * Mark one notification read, then redirect to its link.
     */
    public function open(string $id): void
    {
        $userId = (int) Auth::id();
        $row = Database::getInstance()->fetch(
            'SELECT id, link FROM notifications WHERE id = ? AND (user_id = ? OR user_id IS NULL) LIMIT 1',
            [(int) $id, $userId]
        );

        if ($row) {
            NotificationService::markRead((int) $row['id'], $userId);
            $link = trim((string) ($row['link'] ?? ''));
            redirect($link !== '' ? $link : Auth::landingPath());
        }

        redirect(Auth::landingPath());
    }

    public function read(string $id): void
    {
        if (!Auth::check()) {
            Response::error('Unauthenticated.', [], 401);
        }
        $this->verifyCsrf();
        $ok = NotificationService::markRead((int) $id, (int) Auth::id());
        if (!$ok) {
            Response::error('Notification not found.', [], 404);
        }
        Response::success('Marked as read.');
    }

    public function readAll(): void
    {
        if (!Auth::check()) {
            Response::error('Unauthenticated.', [], 401);
        }
        $this->verifyCsrf();
        NotificationService::markAllRead((int) Auth::id());
        Response::success('All notifications cleared.');
    }
}
