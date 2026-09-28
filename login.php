<?php
require_once __DIR__ . '/includes/auth.php';

// Redirect logged-in users directly to the dashboard
if (current_user()) {
    redirect(BASE_URL . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Query active user details including role and service names
    $stmt = db()->prepare(
        'SELECT u.*, r.name AS role_name, s.name AS service_name 
         FROM users u 
         JOIN roles r ON r.id = u.role_id 
         LEFT JOIN services s ON s.id = u.service_id 
         WHERE u.staff_id = ? AND u.status = "Active"'
    );
    $stmt->execute([trim((string) post('staff_id'))]);
    $user = $stmt->fetch();

    // Verify credentials and authenticate user
    if ($user && password_verify((string) post('password'), $user['password_hash'])) {
        login_user($user);

        db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')
          ->execute([$user['id']]);

        audit('LOGIN', 'users', (int) $user['id'], 'Successful login');

        redirect(BASE_URL . '/dashboard.php');
    }

    $error = 'Invalid staff ID or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | CHMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="brand justify-content-center">
            <img class="brand-logo" src="<?= BASE_URL ?>/images/santarosalogo.png" alt="Santa Rosa city seal">
            <div>
                <strong>CHMS</strong>
                <small>Santa Rosa CHO</small>
            </div>
        </div>

        <h2>Staff Sign In</h2>
        <p class="text-muted">Secure access for authorized City Health Office personnel.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="staff_id" class="form-label">Staff ID</label>
                <input class="form-control" id="staff_id" name="staff_id" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign in</button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/public/">Public Health Portal</a>
        </div>
    </div>
</body>
</html>
