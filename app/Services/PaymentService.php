<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * Records payments (money in/out) for sales, purchases, parties, etc.
 *
 * @package App\Services
 */
final class PaymentService
{
    /**
     * Record a payment and return the inserted id.
     */
    public static function record(
        string $payableType,
        int $payableId,
        string $direction,
        float $amount,
        string $method = 'cash',
        string $partyType = 'none',
        ?int $partyId = null,
        ?string $note = null,
        ?string $date = null
    ): int {
        return Database::getInstance()->insert('payments', [
            'reference'    => generate_code('PAY'),
            'payable_type' => $payableType,
            'payable_id'   => $payableId,
            'party_type'   => $partyType,
            'party_id'     => $partyId,
            'direction'    => $direction,
            'amount'       => $amount,
            'method'       => $method,
            'note'         => $note,
            'paid_at'      => $date ?: date('Y-m-d'),
            'created_by'   => Auth::id(),
            'created_at'   => now(),
        ]);
    }
}
