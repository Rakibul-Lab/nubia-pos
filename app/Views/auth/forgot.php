<?php /** @var string|null $status */ ?>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<div class="login-shell" style="grid-template-columns:1fr;">
    <main class="login-panel" style="min-height:100vh;min-height:100dvh;">
        <div class="login-panel-inner login-anim login-anim-4">
            <div class="login-mobile-brand" style="display:flex;">
                <span class="login-mark sm" aria-hidden="true">N</span>
                <span class="login-brand-name">Nubia</span>
            </div>
            <header class="login-panel-head">
                <h2 class="login-panel-title">Reset password</h2>
                <p class="login-panel-sub">We’ll email you a secure reset link.</p>
            </header>
            <?php if (!empty($status)): ?>
                <div class="login-alert" role="alert" style="border-color:rgba(34,197,94,.28);background:rgba(34,197,94,.1);color:#15803d;"><?= e($status) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= url('forgot-password') ?>" class="login-form">
                <?= csrf_field() ?>
                <div class="login-field">
                    <label class="login-label" for="forgotEmail">Email</label>
                    <div class="login-control">
                        <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                        <input type="email" name="email" id="forgotEmail" placeholder="you@company.com" required autofocus>
                    </div>
                </div>
                <button type="submit" class="login-submit" style="margin-top:8px;">
                    <span>Send reset link</span>
                    <span class="material-symbols-rounded" aria-hidden="true">send</span>
                </button>
            </form>
            <div class="login-demo" style="border:0;padding-top:18px;margin-top:18px;">
                <a class="login-link" href="<?= url('login') ?>">← Back to sign in</a>
            </div>
        </div>
    </main>
</div>
