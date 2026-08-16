<?php
/** @var string $content */
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= \App\Core\View::partial('layouts.partials.head', ['title' => $title ?? 'Welcome']) ?>
</head>
<body>
    <?= $content ?>
    <?= \App\Core\View::partial('layouts.partials.scripts', get_defined_vars()) ?>
</body>
</html>
