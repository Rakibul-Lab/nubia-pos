<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Measurement unit model.
 *
 * @package App\Models
 */
final class Unit extends Model
{
    protected string $table = 'units';

    protected array $fillable = ['name', 'short_name', 'base_unit', 'operator', 'operation_value', 'status'];
}
