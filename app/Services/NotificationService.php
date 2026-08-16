<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * System + user notifications (topbar bell).
 *
 * @package App\Services
 */
final class NotificationService
{
    /**
     * Refresh system alerts and return unread notifications for the current user.
     *
     * @return array{items:array<int,array<string,mixed>>,unread:int}
     */
    public static function forCurrentUser(int $limit = 12): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return ['items' => [], 'unread' => 0];
        }

        self::syncSystemAlerts($userId);

        $db = Database::getInstance();
        $items = $db->fetchAll(
            'SELECT * FROM notifications
             WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)
             ORDER BY id DESC
             LIMIT ' . (int) $limit,
            [$userId]
        );
        $unread = (int) $db->scalar(
            'SELECT COUNT(*) FROM notifications
             WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)',
            [$userId]
        );

        return ['items' => $items, 'unread' => $unread];
    }

    /**
     * Create / clear system-generated alerts for this user.
     */
    public static function syncSystemAlerts(int $userId): void
    {
        $db = Database::getInstance();

        $lowStock = Auth::can('reports.stock')
            ? (int) $db->scalar(
                'SELECT COUNT(*) FROM (
                    SELECT p.id
                    FROM products p
                    LEFT JOIN stock s ON s.product_id = p.id
                    WHERE p.status = 1
                    GROUP BY p.id, p.alert_quantity
                    HAVING COALESCE(SUM(s.quantity), 0) <= p.alert_quantity
                 ) t'
            )
            : 0;

        self::upsertSystemAlert(
            $userId,
            'low_stock',
            $lowStock > 0,
            $lowStock === 1
                ? '1 product is low on stock'
                : $lowStock . ' products are low on stock',
            'Restock items that fell to or below alert quantity.',
            'warning',
            '/reports/stock'
        );

        $dueTotal = Auth::can('accounting.receivables')
            ? (float) $db->scalar(
                'SELECT COALESCE(SUM(due), 0) FROM sales WHERE due > 0 AND status != "cancelled"'
            )
            : 0.0;
        $dueCount = Auth::can('accounting.receivables')
            ? (int) $db->scalar(
                'SELECT COUNT(*) FROM sales WHERE due > 0 AND status != "cancelled"'
            )
            : 0;

        self::upsertSystemAlert(
            $userId,
            'receivables',
            $dueCount > 0,
            $dueCount === 1
                ? '1 sale has pending due'
                : $dueCount . ' sales have pending due',
            'Outstanding collections: ' . money($dueTotal, false),
            'request_quote',
            '/accounting/receivables'
        );
    }

    /**
     * Keep one system alert per type per user.
     * Dismissed (read) alerts stay hidden until the condition clears, then can fire again.
     */
    private static function upsertSystemAlert(
        int $userId,
        string $type,
        bool $active,
        string $title,
        string $body,
        string $icon,
        string $link
    ): void {
        $db = Database::getInstance();
        $existing = $db->fetch(
            'SELECT id, is_read FROM notifications
             WHERE user_id = ? AND type = ?
             ORDER BY id DESC LIMIT 1',
            [$userId, $type]
        );

        if (!$active) {
            // Clear so the alert can reappear the next time the condition is true.
            if ($existing) {
                $db->delete('notifications', ['id' => (int) $existing['id']]);
            }
            return;
        }

        if ($existing) {
            if ((int) $existing['is_read'] === 0) {
                $db->update('notifications', [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => $icon,
                    'link'  => $link,
                ], ['id' => (int) $existing['id']]);
            }
            // Already dismissed while still active — leave it until condition clears.
            return;
        }

        $db->insert('notifications', [
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'icon'    => $icon,
            'link'    => $link,
            'type'    => $type,
            'is_read' => 0,
        ]);
    }

    public static function markRead(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            'SELECT id FROM notifications WHERE id = ? AND (user_id = ? OR user_id IS NULL) LIMIT 1',
            [$id, $userId]
        );
        if (!$row) {
            return false;
        }
        $db->update('notifications', ['is_read' => 1], ['id' => $id]);
        return true;
    }

    public static function markAllRead(int $userId): void
    {
        Database::getInstance()->query(
            'UPDATE notifications SET is_read = 1
             WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)',
            [$userId]
        );
    }
}
