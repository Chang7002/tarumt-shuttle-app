<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $db->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                session_write_close();

                header("Location: index.php");
                exit();
            }
        }
        $error = "Invalid email address or password.";
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<div class="container mt-4" style="max-width: 500px;">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="bg-primary text-white p-4 text-center">
            <i class="bi bi-person-circle display-5 mb-2"></i>
            <h4 class="fw-bold mb-0">Welcome Back</h4>
            <p class="small text-white-50 mb-0">Sign in to book your campus shuttle tickets</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 bg-light" placeholder="student@tarumt.edu.my" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </form>

            <div class="text-center pt-2 border-top">
                <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="text-primary fw-semibold text-decoration-none">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
