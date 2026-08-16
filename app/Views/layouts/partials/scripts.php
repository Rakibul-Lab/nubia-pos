<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script src="<?= asset_v('js/app.js') ?>"></script>

<?php foreach (flash() as $type => $message): ?>
    <?php if (in_array($type, ['success', 'error', 'warning', 'info'], true)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Nubia.toast(<?= json_encode(is_string($message) ? $message : 'Done') ?>, '<?= $type === 'error' ? 'error' : $type ?>');
            });
        </script>
    <?php endif; ?>
<?php endforeach; ?>
<?php $_SESSION['_flash'] = []; ?>

<?php if (!empty($pageScript)): ?>
    <script><?= $pageScript ?></script>
<?php endif; ?>
