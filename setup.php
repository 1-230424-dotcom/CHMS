<?php
require_once __DIR__ . '/includes/functions.php';

// Check if any user accounts already exist in the database
$exists = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$exists) {
    verify_csrf();

    $staff   = trim((string) post('staff_id'));
    $name    = trim((string) post('full_name'));
    $email   = trim((string) post('email'));
    $pass    = (string) post('password');
    $confirm = (string) post('confirm_password');

    // Validation checks
    if ($staff === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || $pass !== $confirm) {
        $error = 'Complete all fields. Password must be at least 8 characters and match confirmation.';
    } else {
        try {
            // Retrieve default administrator role ID
            $role = db()->query("SELECT id FROM roles WHERE code = 'ADMIN'")->fetchColumn();

            // Insert new administrator account
            $stmt = db()->prepare('INSERT INTO users(staff_id, full_name, email, password_hash, role_id, professional_type, status) VALUES(?, ?, ?, ?, ?, ?, "Active")');
            $stmt->execute([
                $staff,
                $name,
                $email,
                password_hash($pass, PASSWORD_DEFAULT),
                $role,
                'System Administrator'
            ]);

            audit('CREATE', 'users', (int) db()->lastInsertId(), 'Initial administrator created');

            header('Location: ' . BASE_URL . '/login.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Unable to create administrator. Staff ID/email may already exist.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Initial Setup | CHMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card wide">
        <div class="brand justify-content-center">
            <img class="brand-logo" src="<?= BASE_URL ?>/images/santarosalogo.png" alt="Santa Rosa city seal">
            <div>
                <strong>CHMS</strong>
                <small>Initial Administrator Setup</small>
            </div>
        </div>

        <?php if ($exists): ?>
            <div class="alert alert-warning">
                An account already exists. For security, setup is disabled. Delete or rename <code>setup.php</code> after the first setup.
            </div>
            <a class="btn btn-primary w-100" href="<?= BASE_URL ?>/login.php">Go to Login</a>
        <?php else: ?>
            <h2>Create First Administrator</h2>
            <p class="text-muted">No staff accounts are pre-created. Create the first administrator now.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="staff_id" class="form-label">Staff ID</label>
                        <input class="form-control" id="staff_id" name="staff_id" required>
                    </div>
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input class="form-control" type="email" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input class="form-control" type="password" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="col-md-12">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-4">Create Administrator</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>