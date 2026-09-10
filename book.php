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
$receipt_data = null; // Holds booked ticket details for the receipt modal
$selected_route_id = intval($_GET['route_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = intval($_POST['route_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($route_id > 0) {
        // Verify seat availability and retrieve route details
        $stmt = $db->prepare("SELECT route_name, origin, destination, departure_time, price, available_seats FROM routes WHERE id = ?");
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
                $booking_id = $db->insert_id;

                $stmtUpdate = $db->prepare("UPDATE routes SET available_seats = available_seats - 1 WHERE id = ?");
                $stmtUpdate->bind_param("i", $route_id);
                $stmtUpdate->execute();

                $db->commit();
                $message = "Ticket booked successfully!";

                // Populate receipt details for modal render
                $receipt_data = [
                    'booking_id'     => $booking_id,
                    'ref_no'         => 'TKT-' . strtoupper(substr(md5((string)$booking_id), 0, 8)),
                    'passenger'      => $_SESSION['user_name'] ?? 'Student',
                    'route_name'     => $route['route_name'],
                    'origin'         => $route['origin'],
                    'destination'    => $route['destination'],
                    'departure_time' => $route['departure_time'],
                    'price'          => $route['price'],
                    'booking_date'   => date('Y-m-d H:i:s')
                ];
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

$routes = $db->query("SELECT * FROM routes WHERE available_seats > 0 ORDER BY route_name ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="container my-4" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2"></i>Book Shuttle Ticket</h5>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i><?= e($message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
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

<?php if ($receipt_data): ?>
<!-- Booking Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="bg-primary text-white p-4 text-center">
                <div class="display-6 mb-1"><i class="bi bi-patch-check-fill text-warning"></i></div>
                <h4 class="fw-bold mb-0">Booking Confirmed!</h4>
                <p class="small text-white-50 mb-0">TAR UMT Shuttle E-Ticket</p>
            </div>
            
            <div class="modal-body p-4" id="printableReceipt">
                <div class="text-center border-bottom pb-3 mb-3">
                    <span class="text-muted small d-block">Ticket Reference</span>
                    <strong class="fs-5 text-primary"><?= e($receipt_data['ref_no']) ?></strong>
                </div>

                <div class="row g-3 small">
                    <div class="col-6">
                        <span class="text-muted d-block">Passenger</span>
                        <strong class="text-dark"><?= e($receipt_data['passenger']) ?></strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted d-block">Date Booked</span>
                        <strong class="text-dark"><?= date('d M Y, H:i', strtotime($receipt_data['booking_date'])) ?></strong>
                    </div>

                    <div class="col-12 border-top pt-2">
                        <span class="text-muted d-block">Shuttle Route</span>
                        <strong class="text-dark fs-6"><?= e($receipt_data['route_name']) ?></strong>
                    </div>

                    <div class="col-12">
                        <span class="text-muted d-block">Journey Details</span>
                        <span class="fw-semibold text-dark"><?= e($receipt_data['origin']) ?></span> 
                        <i class="bi bi-arrow-right mx-1 text-primary"></i> 
                        <span class="fw-semibold text-dark"><?= e($receipt_data['destination']) ?></span>
                    </div>

                    <div class="col-6 border-top pt-2">
                        <span class="text-muted d-block">Departure Time</span>
                        <strong class="text-dark fs-6"><i class="bi bi-clock me-1"></i><?= date('g:i A', strtotime($receipt_data['departure_time'])) ?></strong>
                    </div>
                    <div class="col-6 text-end border-top pt-2">
                        <span class="text-muted d-block">Total Paid</span>
                        <strong class="text-success fs-5">RM<?= number_format((float)$receipt_data['price'], 2) ?></strong>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 text-center mt-3 border border-dashed">
                    <i class="bi bi-qr-code display-6 d-block mb-1 text-secondary"></i>
                    <span class="text-muted" style="font-size: 0.75rem;">Show this digital receipt to the bus driver upon boarding.</span>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 d-flex justify-content-between p-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
                <div>
                    <a href="history.php" class="btn btn-outline-primary btn-sm me-1">View History</a>
                    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    receiptModal.show();
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
