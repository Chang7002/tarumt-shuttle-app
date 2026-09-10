<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email address is already registered.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Failed to create account. Please try again.";
            }
        }
    }
}
?>

<div class="container mt-4" style="max-width: 500px;">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="bg-primary text-white p-4 text-center">
            <i class="bi bi-person-plus-fill display-5 mb-2"></i>
            <h4 class="fw-bold mb-0">Create Account</h4>
            <p class="small text-white-50 mb-0">Join TAR UMT Shuttle Service today</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2 small" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i><?= e($success) ?> <a href="login.php" class="fw-bold text-success text-decoration-underline">Login here</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="name" class="form-control border-start-0 bg-light" placeholder="John Doe" value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 bg-light" placeholder="student@tarumt.edu.my" value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="password" name="confirm_password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mb-3">
                    <i class="bi bi-person-check-fill me-1"></i> Register Account
                </button>
            </form>

            <div class="text-center pt-2 border-top">
                <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-primary fw-semibold text-decoration-none">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
