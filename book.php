<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = Database::getConnection();
$message = '';
$error = '';
$selected_route_id = intval($_GET['route_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = intval($_POST['route_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($route_id > 0) {
        // Verify seat availability
        $stmt = $db->prepare("SELECT available_seats FROM routes WHERE id = ?");
        $stmt->bind_param("i", $route_id);
        $stmt->execute();
        $route = $stmt->get_result()->fetch_assoc();

        if ($route && $route['available_seats'] > 0) {
            // Transaction: Insert booking and deduct available seat count
            $db->begin_transaction();

            try {
                $stmtInsert = $db->prepare("INSERT INTO bookings (user_id, route_id, seats_booked) VALUES (?, ?, 1)");
                $stmtInsert->bind_param("ii", $user_id, $route_id);
                $stmtInsert->execute();

                $stmtUpdate = $db->prepare("UPDATE routes SET available_seats = available_seats - 1 WHERE id = ?");
                $stmtUpdate->bind_param("i", $route_id);
                $stmtUpdate->execute();

                $db->commit();
                $message = "Booking Successfully!";
            } catch (Exception $e) {
                $db->rollback();
                $error = "An error occurred during booking. Please try again.";
            }
        } else {
            $error = "Sorry, no available seats left on this route.";
        }
    } else {
        $error = "Please select a valid shuttle route.";
    }
}

// Fetch available routes for selection dropdown
$routes = $db->query("SELECT * FROM routes WHERE available_seats > 0 ORDER BY route_name ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="container my-4" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2"></i>Book Shuttle Ticket</h5>
        </div>
        <div class="card-body p-4">
            
            <!-- On-Screen Success Banner -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success py-3 px-3 text-center fw-bold shadow-sm rounded-3 border-0 mb-4 d-flex align-items-center justify-content-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <span>Booking Successfully!</span>
                </div>
            <?php endif; ?>

            <!-- Error Prompt -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small text-center mb-3" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-1"></i><?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="book.php">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Route</label>
                    <select name="route_id" class="form-select" required>
                        <option value="">-- Choose a Route --</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?= e($route['id']) ?>" <?= $selected_route_id === (int)$route['id'] ? 'selected' : '' ?>>
                                <?= e($route['route_name']) ?> (<?= e($route['origin']) ?> → <?= e($route['destination']) ?>) - RM<?= number_format((float)$route['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Passenger Name</label>
                    <input type="text" class="form-control" value="<?= e($_SESSION['user_name'] ?? '') ?>" disabled>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
