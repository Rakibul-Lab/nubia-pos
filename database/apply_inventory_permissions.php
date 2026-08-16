<?php

/**
 * Backward-compatible entry point.
 *
 * The permission system is now synchronized as one canonical catalog so an
 * old partial inventory migration cannot recreate retired broad permissions.
 */

declare(strict_types=1);

require __DIR__ . '/apply_full_permissions.php';
