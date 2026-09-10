<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Require user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

$db = Database::getConnection();
$error = '';
$success = '';

// 2. Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid or expired security token. Please refresh and try again.";
    } else {
        $route_id = $_POST['route_id'] ?? '';
        $passenger_name = trim($_POST['passenger_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $travel_date = $_POST['travel_date'] ?? '';
        $seats = (int)($_POST['seats'] ?? 1);
        $user_id = $_SESSION['user_id'];

        if (empty($route_id) || empty($passenger_name) || empty($email) || empty($travel_date)) {
            $error = "Please fill in all required fields.";
        } else {
            // Insert booking query
            $stmt = $db->prepare("INSERT INTO bookings (user_id, route_id, passenger_name, email, travel_date, seats) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssi", $user_id, $route_id, $passenger_name, $email, $travel_date, $seats);

            if ($stmt->execute()) {
                // Regenerate token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header("Location: history.php?success=booking_complete");
                exit();
            } else {
                $error = "Failed to process booking. Please try again.";
            }
        }
    }
}

// Fetch available routes for the dropdown selector
$routes = $db->query("SELECT * FROM routes WHERE available_seats > 0 ORDER BY route_name ASC");
$selected_route_id = $_GET['route_id'] ?? '';
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="container mt-4" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3"><i class="bi bi-ticket-perforated text-primary me-2"></i>Book Shuttle Ticket</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="book.php">
                <!-- CRITICAL: Hidden CSRF Token Field -->
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Select Route</label>
                    <select name="route_id" class="form-select" required>
                        <option value="">-- Choose Route --</option>
                        <?php while ($r = $routes->fetch_assoc()): ?>
                            <option value="<?= $r['id'] ?>" <?= $selected_route_id == $r['id'] ? 'selected' : '' ?>>
                                <?= e($r['route_name']) ?> (<?= e($r['origin']) ?> → <?= e($r['destination']) ?>) - RM<?= number_format((float)$r['price'], 2) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Passenger Full Name</label>
                    <input type="text" name="passenger_name" class="form-control" value="<?= e($_SESSION['user_name'] ?? '') ?>" placeholder="e.g. John Doe" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" placeholder="student@tarumt.edu.my" required>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Travel Date</label>
                        <input type="date" name="travel_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Number of Seats</label>
                        <input type="number" name="seats" class="form-control" value="1" min="1" max="5" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
