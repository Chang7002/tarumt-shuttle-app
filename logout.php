<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Perform session cleanup
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

include __DIR__ . '/header.php';
?>

<div class="container mt-5" style="max-width: 500px;">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-center">
        <div class="bg-primary text-white p-4">
            <i class="bi bi-box-arrow-right display-4 mb-2"></i>
            <h4 class="fw-bold mb-0">Logged Out Successfully</h4>
            <p class="small text-white-50 mb-0">Thank you for using TAR UMT Shuttle Service</p>
        </div>
        
        <div class="card-body p-4">
            <div class="alert alert-success py-2 small mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> Your session has been safely closed.
            </div>

            <p class="text-secondary small mb-4">You will be automatically redirected to the login page in <span id="countdown" class="fw-bold text-primary">3</span> seconds...</p>

            <div class="d-grid gap-2">
                <a href="login.php" class="btn btn-primary fw-semibold py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login Again
                </a>
                <a href="index.php" class="btn btn-outline-secondary fw-semibold py-2">
                    <i class="bi bi-house-door me-1"></i> Return to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let seconds = 3;
    const countdownEl = document.getElementById('countdown');
    
    const interval = setInterval(() => {
        seconds--;
        if (countdownEl) countdownEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = 'login.php?status=logged_out';
        }
    }, 1000);
</script>

<?php include __DIR__ . '/footer.php'; ?>