<?php

/**
 * Backfill placeholder IMEI / serial rows.
 *
 * Products created before unit tracking can carry stock while having no rows in
 * `product_serials`. Those products cannot be sold through the POS because
 * checkout requires one available unit per quantity. This script creates a
 * placeholder unit for every missing one so the counts line up; replace the
 * placeholders with the real codes from the product page afterwards.
 *
 * Usage:
 *   php database/backfill_serials.php          # dry run, prints what it would do
 *   php database/backfill_serials.php --apply  # writes the rows
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

use App\Core\Database;

$apply = in_array('--apply', $argv, true);
$db    = Database::getInstance();

$rows = $db->fetchAll(
    "SELECT p.id, p.name, p.has_imei, s.warehouse_id, s.quantity,
            (SELECT COUNT(*) FROM product_serials ps
              WHERE ps.product_id = p.id
                AND ps.warehouse_id = s.warehouse_id
                AND ps.status = 'available') AS available
     FROM products p
     JOIN stock s ON s.product_id = p.id
     WHERE (p.has_imei = 1 OR p.has_serial = 1) AND s.quantity > 0
     ORDER BY p.id, s.warehouse_id"
);

/** Placeholder codes must satisfy the unique index on product_serials.imei. */
$nextCode = static function (Database $db, int $productId, int $warehouseId, int $n): string {
    do {
        $code = sprintf('TEMP-%d-%d-%04d', $productId, $warehouseId, $n);
        $taken = $db->scalar(
            'SELECT COUNT(*) FROM product_serials WHERE imei = ? OR serial_no = ?',
            [$code, $code]
        );
        $n++;
    } while ((int) $taken > 0);

    return $code;
};

$created = 0;
$surplus = [];

foreach ($rows as $row) {
    $productId   = (int) $row['id'];
    $warehouseId = (int) $row['warehouse_id'];
    $stock       = (int) floor((float) $row['quantity']);
    $available   = (int) $row['available'];
    $missing     = $stock - $available;

    if ($missing === 0) {
        continue;
    }

    if ($missing < 0) {
        $surplus[] = sprintf(
            '  #%d %s (warehouse %d): %d units on file but only %d in stock',
            $productId,
            $row['name'],
            $warehouseId,
            $available,
            $stock
        );
        continue;
    }

    printf(
        "#%d %s (warehouse %d): stock %d, units %d -> creating %d placeholder%s\n",
        $productId,
        $row['name'],
        $warehouseId,
        $stock,
        $available,
        $missing,
        $missing === 1 ? '' : 's'
    );

    if (!$apply) {
        $created += $missing;
        continue;
    }

    $asImei = (int) $row['has_imei'] === 1;
    for ($i = 1; $i <= $missing; $i++) {
        $code = $nextCode($db, $productId, $warehouseId, $i);
        $db->insert('product_serials', [
            'product_id'       => $productId,
            'variant_id'       => null,
            'serial_no'        => $asImei ? null : $code,
            'imei'             => $asImei ? $code : null,
            'status'           => 'available',
            'warehouse_id'     => $warehouseId,
            'sale_item_id'     => null,
            'purchase_item_id' => null,
        ]);
        $created++;
    }
}

if ($surplus !== []) {
    echo "\nMore units on file than stock (left untouched, review manually):\n";
    echo implode("\n", $surplus), "\n";
}

echo "\n", $apply
    ? "Done. Created {$created} placeholder unit(s).\n"
    : "Dry run. Would create {$created} placeholder unit(s). Re-run with --apply to write them.\n";
