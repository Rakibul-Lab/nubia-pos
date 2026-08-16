<?php
use App\Core\View;
/** @var array $rows @var float $total @var string $amountKey @var string $linkBase @var string $label */
$canOpenParty = $linkBase === 'customers' ? can('customers.view') : can('suppliers.view');
?>
<?= View::partial('components.page_head', ['title' => $title, 'subtitle' => 'Total outstanding: ' . money($total)]) ?>
<?= View::partial('accounting.nav') ?>
<div class="card"><div class="table-wrap"><table class="nubia">
    <thead><tr><th>Name</th><th>Phone</th><th class="text-end"><?= e($label) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td class="fw-800"><?= e($r['name']) ?></td>
            <td class="text-muted-2"><?= e($r['phone'] ?? '—') ?></td>
            <td class="text-end fw-800" style="color:var(--danger)"><?= money((float) $r[$amountKey]) ?></td>
            <td class="text-end"><?php if ($canOpenParty): ?><a href="<?= url($linkBase . '/' . $r['id']) ?>" class="btn btn-soft btn-sm">View</a><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($rows === []): ?><tr><td colspan="4"><?= View::partial('components.empty', ['message' => 'Nothing outstanding']) ?></td></tr><?php endif; ?>
    </tbody>
    <?php if ($rows !== []): ?><tfoot><tr><td colspan="2" class="fw-800">Total</td><td class="text-end fw-800"><?= money($total) ?></td><td></td></tr></tfoot><?php endif; ?>
</table></div></div>
