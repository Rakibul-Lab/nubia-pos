<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Product category (supports parent/sub-categories).
 *
 * @package App\Models
 */
final class Category extends Model
{
    protected string $table = 'categories';

    protected array $fillable = ['parent_id', 'name', 'slug', 'image', 'status'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function withCounts(): array
    {
        return $this->db->fetchAll(
            'SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
             FROM categories c
             LEFT JOIN categories p ON p.id = c.parent_id
             ORDER BY c.name'
        );
    }
}
