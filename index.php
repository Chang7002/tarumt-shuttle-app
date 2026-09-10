<?php
include __DIR__ . '/header.php';
$db = Database::getConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $db->prepare("SELECT * FROM routes WHERE route_name LIKE ? OR origin LIKE ? OR destination LIKE ? ORDER BY departure_time ASC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $routes_result = $stmt->get_result();
} else {
    $routes_result = $db->query("SELECT * FROM routes ORDER BY departure_time ASC");
}

// Fallback images if image_url is missing in database
$default_images = [
    'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1570125909517-53cb21c89ff2?auto=format&fit=crop&w=600&q=80'
];
$img_index = 0;
?>

<div class="container mt-2">
    <div class="p-5 mb-4 bg-primary text-white rounded-3 shadow-sm text-center">
        <h1 class="display-6 fw-bold">TAR UMT Campus Shuttle Bus</h1>
        <p class="lead mb-0">Check schedules, view live seat availability, and book your ride online.</p>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 rounded-3">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control" placeholder="Search by route name, origin, or destination..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-search me-1"></i> Search</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Search"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <h5 class="fw-bold mb-3"><i class="bi bi-bus-front me-2 text-primary"></i>Available Routes</h5>
    <div class="row g-4">
        <?php if ($routes_result && $routes_result->num_rows > 0): ?>
            <?php while($route = $routes_result->fetch_assoc()): 
                $img_src = !empty($route['image_url']) ? $route['image_url'] : $default_images[$img_index % count($default_images)];
                $img_index++;
                $seats_left = (int)($route['available_seats'] ?? 0);
            ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden d-flex flex-column">
                        <img src="<?= e($img_src) ?>" class="card-img-top" alt="<?= e($route['route_name']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1"><?= e($route['route_name']) ?></h6>
                                <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($route['origin']) ?> → <?= e($route['destination']) ?></p>
                                <p class="small text-secondary mb-3">
                                    <i class="bi bi-clock me-1"></i>Departs <?= date('H:i', strtotime($route['departure_time'])) ?> · 
                                    <strong>RM<?= number_format((float)$route['price'], 2) ?></strong> · 
                                    <span class="badge bg-<?= $seats_left > 0 ? 'success' : 'danger' ?>"><?= $seats_left ?> seats left</span>
                                </p>
                            </div>
                            <?php if ($seats_left > 0): ?>
                                <a href="book.php?route_id=<?= (int)$route['id'] ?>" class="btn btn-outline-primary w-100 fw-semibold">Book Ticket</a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 fw-semibold" disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info text-center">No routes found matching your query.</div></div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
