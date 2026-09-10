<?php
include __DIR__ . '/header.php';

// Access control check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit();
}

$db = Database::getConnection();
$user_id = (int)$_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Admin views all history, regular users view only their own bookings
if ($is_admin) {
    $stmt = $db->prepare("
        SELECT b.*, r.route_name, r.origin, r.destination, r.price 
        FROM bookings b 
        JOIN routes r ON b.route_id = r.id 
        ORDER BY b.booking_date DESC
    ");
} else {
    $stmt = $db->prepare("
        SELECT b.*, r.route_name, r.origin, r.destination, r.price 
        FROM bookings b 
        JOIN routes r ON b.route_id = r.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC
    ");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$history = $stmt->get_result();
?>

<div class="container mt-2">
    <div class="mb-3">
        <h3 class="fw-bold mb-1">Reservation History</h3>
        <p class="text-muted small mb-0">Live transaction records pulled directly from the RDS database.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted small">
                        <th class="ps-4">Reference</th>
                        <th>Passenger</th>
                        <th>Route Details</th>
                        <th>Travel Date</th>
                        <th>Seats</th>
                        <th>Total Paid</th>
                        <th class="pe-4">Booking Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history && $history->num_rows > 0): ?>
                        <?php while($row = $history->fetch_assoc()): ?>
                            <?php 
                                $seats = (int)($row['seats'] ?? $row['tickets_booked'] ?? 1);
                                $price = (float)($row['price'] ?? 0);
                                $total_price = $row['total_price'] ?? ($seats * $price);
                                $ref_code = $row['booking_reference'] ?? ('BK-' . str_pad((string)$row['id'], 6, '0', STR_PAD_LEFT));
                            ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-primary"><?= e($ref_code) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($row['passenger_name']) ?></div>
                                    <div class="small text-muted"><?= e($row['email'] ?? $row['passenger_email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($row['route_name']) ?></div>
                                    <div class="small text-muted"><?= e($row['origin']) ?> → <?= e($row['destination']) ?></div>
                                </td>
                                <td><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($row['travel_date'])) ?></td>
                                <td><span class="badge bg-secondary"><?= $seats ?> seat(s)</span></td>
                                <td class="fw-semibold text-success">RM<?= number_format((float)$total_price, 2) ?></td>
                                <td class="pe-4 small text-muted"><?= date('Y-m-d H:i', strtotime($row['booking_date'] ?? 'now')) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No booking records found in system.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
