<?php
include __DIR__ . '/../includes/header.php';

$db = Database::getConnection();
$message = '';
$selected_route = (int)($_GET['route_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert alert-danger">Invalid request security token.</div>';
    } else {
        $route_id    = (int)$_POST['route_id'];
        $name        = trim($_POST['passenger_name']);
        $email       = filter_var(trim($_POST['passenger_email']), FILTER_VALIDATE_EMAIL);
        $travel_date = $_POST['travel_date'];
        $seats       = (int)$_POST['tickets_booked'];

        if (!$email || empty($name) || $seats < 1 || $seats > 5 || empty($travel_date)) {
            $message = '<div class="alert alert-warning">Please fill in all fields correctly (Max 5 seats).</div>';
        } else {
            try {
                // Begin Atomic Transaction
                $db->begin_transaction();

                // Lock row for update to prevent race conditions during high load
                $stmt = $db->prepare("SELECT price, available_seats FROM routes WHERE id = ? FOR UPDATE");
                $stmt->bind_param("i", $route_id);
                $stmt->execute();
                $route = $stmt->get_result()->fetch_assoc();

                if ($route && $route['available_seats'] >= $seats) {
                    $total_price = $route['price'] * $seats;
                    $ref_code = 'TKT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                    // Insert Booking
                    $ins = $db->prepare("INSERT INTO bookings (booking_reference, route_id, passenger_name, passenger_email, tickets_booked, total_price, travel_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $ins->bind_param("sissids", $ref_code, $route_id, $name, $email, $seats, $total_price, $travel_date);
                    $ins->execute();

                    // Update Available Seats
                    $upd = $db->prepare("UPDATE routes SET available_seats = available_seats - ? WHERE id = ?");
                    $upd->bind_param("ii", $seats, $route_id);
                    $upd->execute();

                    $db->commit();
                    $message = "<div class='alert alert-success'>Booking Confirmed! Ref: <strong>" . e($ref_code) . "</strong>. <a href='history.php' class='alert-link'>View History</a></div>";
                } else {
                    $db->rollback();
                    $message = '<div class="alert alert-danger">Insufficient seats remaining for this route.</div>';
                }
            } catch (Exception $ex) {
                $db->rollback();
                error_log("Transaction Failed: " . $ex->getMessage());
                $message = '<div class="alert alert-danger">System error processing transaction. Please try again.</div>';
            }
        }
    }
}

$routes = $db->query("SELECT id, route_name, price, available_seats FROM routes WHERE available_seats > 0 ORDER BY departure_time ASC");
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h4 class="card-title fw-bold text-center mb-4">Reserve Your Seat</h4>
                    <?= $message ?>
                    <form action="book.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Route</label>
                            <select name="route_id" class="form-select" required>
                                <?php while($r = $routes->fetch_assoc()): ?>
                                    <option value="<?= $r['id'] ?>" <?= $selected_route === (int)$r['id'] ? 'selected' : '' ?>>
                                        <?= e($r['route_name']) ?> - RM<?= number_format((float)$r['price'], 2) ?> (<?= $r['available_seats'] ?> left)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="passenger_name" class="form-control" placeholder="Alex Tan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Student Email</label>
                            <input type="email" name="passenger_email" class="form-control" placeholder="student@tarc.edu.my" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Travel Date</label>
                                <input type="date" name="travel_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Seats (Max 5)</label>
                                <input type="number" name="tickets_booked" class="form-control" min="1" max="5" value="1" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2">Confirm Reservation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>