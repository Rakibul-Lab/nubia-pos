<?php
/** @var string $content */
/** @var string $title */
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="<?= e(setting('language', 'en')) ?>">
<head>
    <?= View::partial('layouts.partials.head', ['title' => $title ?? 'Dashboard']) ?>
</head>
<body>
<div class="app-shell">
    <?= View::partial('layouts.partials.sidebar') ?>
    <div class="main-wrap">
        <?= View::partial('layouts.partials.topbar') ?>
        <main class="content">
            <?= $content ?>
        </main>
    </div>
</div>
<?= View::partial('layouts.partials.bottomnav') ?>
<?= View::partial('layouts.partials.scripts', get_defined_vars()) ?>
</body>
</html>
