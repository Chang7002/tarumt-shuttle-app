<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$query = "SELECT b.*, r.route_name, r.origin, r.destination 
          FROM bookings b 
          JOIN routes r ON b.route_id = r.id 
          ORDER BY b.booking_date DESC";
$history = $db->query($query);
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
                        <tr>
                            <td class="ps-4 font-monospace fw-bold text-primary"><?= e($row['booking_reference']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($row['passenger_name']) ?></div>
                                <div class="small text-muted"><?= e($row['passenger_email']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($row['route_name']) ?></div>
                                <div class="small text-muted"><?= e($row['origin']) ?> → <?= e($row['destination']) ?></div>
                            </td>
                            <td><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($row['travel_date'])) ?></td>
                            <td><span class="badge bg-secondary"><?= $row['tickets_booked'] ?> seat(s)</span></td>
                            <td class="fw-semibold text-success">RM<?= number_format((float)$row['total_price'], 2) ?></td>
                            <td class="pe-4 small text-muted"><?= date('Y-m-d H:i', strtotime($row['booking_date'])) ?></td>
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
