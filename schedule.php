<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$routes = $db->query("SELECT * FROM routes ORDER BY departure_time ASC");
?>

<div class="container mt-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Shuttle Departure Timetable</h3>
            <p class="text-muted small mb-0">Daily active schedules for TAR UMT campus shuttles.</p>
        </div>
        <a href="book.php" class="btn btn-primary btn-sm fw-semibold"><i class="bi bi-ticket-perforated me-1"></i> Book Ticket</a>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted small">
                        <th class="ps-4">Departure</th>
                        <th>Route Name</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Price</th>
                        <th>Seats Left</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($routes && $routes->num_rows > 0): ?>
                        <?php while($r = $routes->fetch_assoc()): 
                            $seats = (int)($r['available_seats'] ?? 0);
                            $badge_class = $seats > 5 ? 'bg-success' : ($seats > 0 ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($r['departure_time'])) ?>
                            </td>
                            <td class="fw-semibold"><?= e($r['route_name']) ?></td>
                            <td><?= e($r['origin']) ?></td>
                            <td><?= e($r['destination']) ?></td>
                            <td>RM<?= number_format((float)$r['price'], 2) ?></td>
                            <td>
                                <span class="badge <?= $badge_class ?>">
                                    <?= $seats > 0 ? $seats . ' left' : 'Sold Out' ?>
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if ($seats > 0): ?>
                                    <a href="book.php?route_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold px-3">Reserve</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary fw-semibold px-3" disabled>Full</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No schedules active at this time.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
