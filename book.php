<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = Database::getConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = "Booking Successfully!";
}

$routes = $db->query("SELECT * FROM routes ORDER BY route_name ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="container my-4" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2"></i>Book Shuttle Ticket</h5>
        </div>
        <div class="card-body p-4">
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success py-3 px-3 text-center fw-bold shadow-sm rounded-3 border-0 mb-4 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <span><?= e($message) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="book.php">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Route</label>
                    <select name="route_id" class="form-select" required>
                        <option value="">-- Choose a Route --</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?= e($route['id']) ?>">
                                <?= e($route['route_name']) ?> (<?= e($route['origin']) ?> → <?= e($route['destination']) ?>) - RM<?= number_format((float)$route['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
