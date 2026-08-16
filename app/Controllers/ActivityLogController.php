<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;

/**
 * Activity / audit log viewer.
 *
 * @package App\Controllers
 */
final class ActivityLogController extends Controller
{
    public function index(): void
    {
        $this->authorize('logs.view');
        $model  = new ActivityLog();
        $page   = $this->request->int('page', 1);
        $result = $model->paginate(
            'SELECT a.*, u.name AS user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC',
            [], $page, 25
        );
        $this->view('logs.index', [
            'title' => 'Activity Logs',
            'logs'  => $result['data'],
            'meta'  => $result,
        ]);
    }
}
