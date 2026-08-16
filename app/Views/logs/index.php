<?php
use App\Core\View;
/** @var array $logs @var array $meta */
?>
<?= View::partial('components.page_head', ['title' => 'Activity Logs', 'subtitle' => 'Audit trail of user actions']) ?>
<div class="card"><div class="table-wrap"><table class="nubia">
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $l): ?>
        <tr>
            <td class="text-muted-2" style="font-size:.82rem;"><?= date('M j, Y g:i A', strtotime($l['created_at'])) ?></td>
            <td class="fw-800"><?= e($l['user_name'] ?? 'System') ?></td>
            <td><span class="badge-pill badge-info"><?= e($l['action']) ?></span></td>
            <td class="text-muted-2"><?= e($l['description'] ?? '') ?></td>
            <td class="text-muted-2" style="font-size:.82rem;"><?= e($l['ip_address'] ?? '—') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($logs === []): ?><tr><td colspan="5"><?= View::partial('components.empty', ['message' => 'No activity recorded yet']) ?></td></tr><?php endif; ?>
    </tbody>
</table></div>
<?php if (($meta['last_page'] ?? 1) > 1): ?><div class="card-footer"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('activity-logs')]) ?></div><?php endif; ?>
</div>
