<?php
include __DIR__ . '/header.php';

// Access Control: Block non-admin users or guests
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

$db = Database::getConnection();

// Fetch summary metrics for admin dashboard overview
$total_users = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc()['total'] ?? 0;
$total_routes = $db->query("SELECT COUNT(*) AS total FROM routes")->fetch_assoc()['total'] ?? 0;
$total_bookings = $db->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'] ?? 0;

// Fetch all student bookings
$bookings_query = "
    SELECT b.id, b.passenger_name, b.email, b.travel_date, b.seats, b.created_at, r.route_name, r.origin, r.destination 
    FROM bookings b
    JOIN routes r ON b.route_id = r.id
    ORDER BY b.created_at DESC
";
$bookings = $db->query($bookings_query);
?>

<div class="container mt-2">
    <!-- Admin Hero Banner -->
    <div class="p-4 mb-4 bg-dark text-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Admin Dashboard</h2>
            <p class="text-white-50 mb-0">Welcome back, <?= e($_SESSION['user_name'] ?? 'Administrator') ?>!</p>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 fw-semibold">Admin Mode</span>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-3 p-3 me-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small">Registered Students</h6>
                        <h3 class="fw-bold mb-0"><?= $total_users ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-3 p-3 me-3">
                        <i class="bi bi-bus-front-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small">Active Routes</h6>
                        <h3 class="fw-bold mb-0"><?= $total_routes ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded-3 p-3 me-3">
                        <i class="bi bi-ticket-detailed-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small">Total Bookings</h6>
                        <h3 class="fw-bold mb-0"><?= $total_bookings ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="bi bi-receipt text-primary me-2"></i>All Student Bookings</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Passenger</th>
                        <th>Route</th>
                        <th>Travel Date</th>
                        <th>Seats</th>
                        <th>Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings && $bookings->num_rows > 0): ?>
                        <?php while ($row = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['id'] ?></td>
                                <td>
                                    <div><?= e($row['passenger_name']) ?></div>
                                    <small class="text-muted"><?= e($row['email']) ?></small>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= e($row['route_name']) ?></span>
                                    <div class="small text-muted"><?= e($row['origin']) ?> → <?= e($row['destination']) ?></div>
                                </td>
                                <td><?= date('d M Y', strtotime($row['travel_date'])) ?></td>
                                <td><span class="badge bg-secondary"><?= $row['seats'] ?> seat(s)</span></td>
                                <small class="text-muted"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></small>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No student bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
