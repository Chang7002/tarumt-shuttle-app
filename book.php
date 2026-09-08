<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$error = '';
$success = '';

// Get route ID from URL
$route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;

// Handle Form Submission (When clicking "Confirm Booking")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF Token
    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid or expired security token. Please refresh and try again.";
    } else {
        $route_id = (int)$_POST['route_id'];
        $passenger_name = trim($_POST['passenger_name']);
        $passenger_email = trim($_POST['passenger_email']);
        $travel_date = $_POST['travel_date'];
        $tickets_booked = (int)$_POST['tickets_booked'];

        if (empty($passenger_name) || empty($passenger_email) || empty($travel_date) || $tickets_booked < 1) {
            $error = "Please fill in all required fields properly.";
        } else {
            // Fetch route details
            $stmt = $db->prepare("SELECT price, available_seats FROM routes WHERE id = ?");
            $stmt->bind_param("i", $route_id);
            $stmt->execute();
            $route = $stmt->get_result()->fetch_assoc();

            if (!$route) {
                $error = "Selected route does not exist.";
            } elseif ($route['available_seats'] < $tickets_booked) {
                $error = "Not enough available seats for this route.";
            } else {
                // Calculate total price and generate reference code
                $total_price = $route['price'] * $tickets_booked;
                $booking_ref = 'SHUTTLE-' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 6));

                // Start database transaction
                $db->begin_transaction();
                try {
                    // Insert booking
                    $insert = $db->prepare("INSERT INTO bookings (booking_reference, route_id, passenger_name, passenger_email, travel_date, tickets_booked, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insert->bind_param("sisssid", $booking_ref, $route_id, $passenger_name, $passenger_email, $travel_date, $tickets_booked, $total_price);
                    $insert->execute();

                    // Deduct available seats
                    $update = $db->prepare("UPDATE routes SET available_seats = available_seats - ? WHERE id = ?");
                    $update->bind_param("ii", $tickets_booked, $route_id);
                    $update->execute();

                    $db->commit();
                    $success = "Booking successful! Your reference ID is: <strong>" . e($booking_ref) . "</strong>";
                } catch (Exception $e) {
                    $db->rollback();
                    $error = "Failed to process booking. Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all available routes for selector
$routes_list = $db->query("SELECT * FROM routes ORDER BY route_name ASC");
?>

<div class="container mt-2" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-3 p-4">
        <h4 class="fw-bold mb-3"><i class="bi bi-ticket-perforated text-primary me-2"></i>Book Shuttle Ticket</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small"><?= $success ?></div>
            <a href="history.php" class="btn btn-outline-primary w-100 fw-semibold mt-2">View My Booking History</a>
        <?php else: ?>

        <form method="POST" action="book.php">
            <!-- CSRF Protection Field -->
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold small">Select Route</label>
                <select name="route_id" class="form-select" required>
                    <option value="">-- Choose Route --</option>
                    <?php while($r = $routes_list->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $route_id ? 'selected' : '' ?>>
                            <?= e($r['route_name']) ?> (RM<?= number_format((float)$r['price'], 2) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Passenger Full Name</label>
                <input type="text" name="passenger_name" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Email Address</label>
                <input type="email" name="passenger_email" class="form-control" placeholder="student@tarumt.edu.my" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Travel Date</label>
                    <input type="date" name="travel_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Number of Seats</label>
                    <input type="number" name="tickets_booked" class="form-control" value="1" min="1" max="5" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">Confirm Booking</button>
        </form>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
