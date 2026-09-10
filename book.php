<?php
// 1. Include header FIRST (it handles session_start() cleanly)
include __DIR__ . '/header.php';

// 2. Access control check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit();
}

$db = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'] ?? '';
    $passenger_name = trim($_POST['passenger_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $travel_date = $_POST['travel_date'] ?? '';
    $seats = (int)($_POST['seats'] ?? 1);
    $user_id = $_SESSION['user_id'];

    if (empty($route_id) || empty($passenger_name) || empty($email) || empty($travel_date)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $db->prepare("INSERT INTO bookings (user_id, route_id, passenger_name, email, travel_date, seats) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $user_id, $route_id, $passenger_name, $email, $travel_date, $seats);

        if ($stmt->execute()) {
            // Deduct seats
            $db->query("UPDATE routes SET available_seats = available_seats - $seats WHERE id = $route_id");
            echo "<script>window.location.href='history.php?success=1';</script>";
            exit();
        } else {
            $error = "Booking failed: " . $db->error;
        }
    }
}

// Fetch routes
$routes_result = $db->query("SELECT * FROM routes WHERE available_seats > 0 ORDER BY departure_time ASC");
?>

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
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Select Route</label>
                    <select name="route_id" class="form-select" required>
                        <option value="">-- Choose Route --</option>
                        <?php if ($routes_result && $routes_result->num_rows > 0): ?>
                            <?php while ($r = $routes_result->fetch_assoc()): ?>
                                <option value="<?= $r['id'] ?>">
                                    <?= e($r['route_name']) ?> (<?= e($r['origin']) ?> → <?= e($r['destination']) ?>) - RM<?= number_format((float)$r['price'], 2) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Passenger Full Name</label>
                    <input type="text" name="passenger_name" class="form-control" value="<?= e($_SESSION['user_name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control" value="student@tarumt.edu.my" required>
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
