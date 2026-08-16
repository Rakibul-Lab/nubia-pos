<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Product brand model.
 *
 * @package App\Models
 */
final class Brand extends Model
{
    protected string $table = 'brands';

    protected array $fillable = ['name', 'slug', 'logo', 'status'];
}
