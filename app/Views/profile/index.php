<?php
use App\Core\View;
/** @var array $user */
?>
<?= View::partial('components.page_head', ['title' => 'My Profile', 'subtitle' => 'Manage your account details']) ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body text-center">
            <div class="avatar xl mx-auto mb-3"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
            <h4 class="fw-800 mb-1"><?= e($user['name']) ?></h4>
            <p class="text-muted-2 mb-2"><?= e($user['email']) ?></p>
            <span class="badge-pill badge-info"><?= e($user['role_name'] ?? 'User') ?></span>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header">Account Details</div><div class="card-body">
            <form method="POST" action="<?= url('profile') ?>"><?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"></div>
                </div>
                <div class="mt-3"><button class="btn btn-brand"><span class="material-symbols-rounded">save</span> Update Profile</button></div>
            </form>
        </div></div>
        <div class="card"><div class="card-header">Change Password</div><div class="card-body">
            <form method="POST" action="<?= url('profile/password') ?>"><?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-12"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                </div>
                <div class="mt-3"><button class="btn btn-brand"><span class="material-symbols-rounded">lock</span> Change Password</button></div>
            </form>
        </div></div>
    </div>
</div>
