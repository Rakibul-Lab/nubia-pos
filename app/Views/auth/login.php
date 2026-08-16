<?php
/** @var string|null $error */
$appName = setting('business_name', config('app.name', 'Nubia Inventory'));
$year = date('Y');
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<div class="login-shell" data-theme-lock>
    <aside class="login-stage" aria-hidden="false">
        <div class="login-stage-bg" aria-hidden="true">
            <div class="login-stage-mesh"></div>
            <div class="login-stage-grid"></div>
            <div class="login-stage-orb login-stage-orb-a"></div>
            <div class="login-stage-orb login-stage-orb-b"></div>
        </div>

        <div class="login-stage-inner">
            <div class="login-brand-lockup login-anim login-anim-1">
                <span class="login-mark" aria-hidden="true">N</span>
                <div class="login-brand-copy">
                    <span class="login-brand-name">Nubia</span>
                    <span class="login-brand-tag">Inventory · POS</span>
                </div>
            </div>

            <div class="login-stage-hero login-anim login-anim-2">
                <h1 class="login-stage-title">Nubia</h1>
                <p class="login-stage-lead">Inventory and POS, tuned for the pace of real retail.</p>
            </div>

            <div class="login-stage-foot login-anim login-anim-3">
                <span>© <?= $year ?> <?= e($appName) ?></span>
            </div>
        </div>
    </aside>

    <main class="login-panel">
        <div class="login-panel-inner login-anim login-anim-4">
            <div class="login-mobile-brand">
                <span class="login-mark sm" aria-hidden="true">N</span>
                <span class="login-brand-name">Nubia</span>
            </div>

            <header class="login-panel-head">
                <h2 class="login-panel-title">Sign in</h2>
                <p class="login-panel-sub">Access your workspace securely.</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="login-alert" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('login') ?>" class="login-form" id="loginForm" autocomplete="on">
                <?= csrf_field() ?>

                <div class="login-field">
                    <label class="login-label" for="loginEmail">Email</label>
                    <div class="login-control">
                        <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                        <input type="email" name="email" id="loginEmail" placeholder="you@company.com" value="<?= old('email') ?>" required autofocus>
                    </div>
                </div>

                <div class="login-field">
                    <div class="login-label-row">
                        <label class="login-label" for="loginPassword">Password</label>
                        <a class="login-link" href="<?= url('forgot-password') ?>">Forgot?</a>
                    </div>
                    <div class="login-control">
                        <span class="material-symbols-rounded" aria-hidden="true">lock</span>
                        <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                        <button type="button" class="login-eye" id="loginPwToggle" aria-label="Show password">
                            <span class="material-symbols-rounded" id="loginPwIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <label class="login-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in</span>
                </label>

                <button type="submit" class="login-submit">
                    <span>Continue</span>
                    <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span>
                </button>
            </form>
        </div>
    </main>
</div>

<?php $pageScript = <<<'JS'
(function () {
    const btn = document.getElementById('loginPwToggle');
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('loginPwIcon');
    if (!btn || !input || !icon) return;
    btn.addEventListener('click', function () {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.textContent = show ? 'visibility_off' : 'visibility';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
})();
JS; ?>
