<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Key/value application settings.
 *
 * @package App\Models
 */
final class Setting extends Model
{
    protected string $table = 'settings';

    /**
     * @return array<string,string>
     */
    public function allKeyed(): array
    {
        $rows = $this->db->fetchAll('SELECT `key`, `value` FROM settings');
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['key']] = $row['value'];
        }
        return $out;
    }

    public function put(string $key, ?string $value, string $group = 'general'): void
    {
        $this->db->query(
            'INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value, $group]
        );
    }
}
