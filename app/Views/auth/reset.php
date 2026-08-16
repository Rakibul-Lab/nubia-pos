<?php /** @var string $token @var string $email */ ?>
<div style="min-height:100vh;display:grid;place-items:center;padding:24px;">
    <div class="card animate-in" style="width:100%;max-width:420px;">
        <div class="card-body p-4">
            <h4 class="fw-800 mb-1">Choose a new password</h4>
            <p class="text-muted-2 mb-4">Your new password must be at least 8 characters.</p>
            <form method="POST" action="<?= url('reset-password') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                </div>
                <button class="btn btn-brand w-100">Update password</button>
            </form>
        </div>
    </div>
</div>
