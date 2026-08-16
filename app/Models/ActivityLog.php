<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use App\Core\Request;

/**
 * Activity / audit log model.
 *
 * @package App\Models
 */
final class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    /**
     * Record an activity entry.
     */
    public static function record(
        string $action,
        ?string $module = null,
        ?string $description = null,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        try {
            $request = new Request();
            (new self())->create([
                'user_id'      => Auth::id(),
                'action'       => $action,
                'module'       => $module,
                'description'  => $description,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'ip_address'   => $request->ip(),
                'user_agent'   => mb_substr($request->userAgent(), 0, 255),
                'created_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Logging must never break the request.
        }
    }
}
