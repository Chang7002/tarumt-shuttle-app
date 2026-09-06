# tarumt-shuttle-app
#!/bin/bash
dnf update -y
dnf install -y httpd php php-mysqli mariadb105

systemctl start httpd
systemctl enable httpd

# -------------------------------------------------------------
# 1. DATABASE CONFIGURATION (db.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/db.php
<?php
$host = 'shuttle-db-instance.cxahvxc84ilj.us-east-1.rds.amazonaws.com';
$user = 'admin';
$password = 'Tarumt2026Pass!';
$dbname = 'shuttle_db';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) { die("Database Connection Failed: " . $conn->connect_error); }

function get_instance_id() {
    $id = @file_get_contents('http://169.254.169.254/latest/meta-data/instance-id');
    return $id ? $id : 'EC2-LocalHost';
}
?>
EOF

# -------------------------------------------------------------
# 2. HEADER & NAVIGATION TEMPLATE (header.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/header.php
<?php
require_once 'db.php';
$instance_id = get_instance_id();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAR UMT Campus Shuttle Bus Ticketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --tarumt-blue: #1a56db; --tarumt-dark-blue: #1e40af; }
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; color: #1e293b; min-height: 100vh; display: flex; flex-direction: column; }
        .main-wrapper { flex: 1; }
        .navbar-tarumt { background-color: var(--tarumt-blue); padding: 0.75rem 1.5rem; }
        .navbar-tarumt .navbar-brand { font-weight: 700; color: #fff; }
        .navbar-tarumt .nav-link { color: rgba(255, 255, 255, 0.85); font-weight: 500; font-size: 0.9rem; padding: 0.4rem 0.8rem; border-radius: 6px; }
        .navbar-tarumt .nav-link:hover, .navbar-tarumt .nav-link.active { color: #fff; background-color: rgba(255, 255, 255, 0.15); }
        .brand-badge { background-color: #d97706; color: white; font-weight: 800; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-right: 8px; }
        .hero-card { background-color: var(--tarumt-blue); color: white; border-radius: 12px; padding: 3rem 2rem; text-align: center; margin-bottom: 2rem; }
        .card-route { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; }
        .card-route img { height: 160px; object-fit: cover; width: 100%; }
        .btn-tarumt { background-color: var(--tarumt-blue); color: white; border-radius: 6px; font-weight: 600; font-size: 0.9rem; }
        .btn-tarumt:hover { background-color: var(--tarumt-dark-blue); color: white; }
        .node-badge { background-color: rgba(255, 255, 255, 0.2); color: white; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; }
        footer { background-color: #fff; border-top: 1px solid #e2e8f0; padding: 2rem 0; margin-top: 3rem; font-size: 0.875rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-tarumt">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="brand-badge">TARUMT</span> Campus Shuttle Bus Ticketing
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#routes">Routes</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'schedule.php' ? 'active' : ''; ?>" href="schedule.php">Schedule</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'history.php' ? 'active' : ''; ?>" href="history.php"><i class="bi bi-clock-history me-1"></i>Booking History</a></li>
                <li class="nav-item me-2"><a class="nav-link btn btn-sm btn-outline-light px-3 <?php echo $current_page == 'book.php' ? 'active' : ''; ?>" href="book.php">Book Ticket</a></li>
                <li class="nav-item"><span class="node-badge"><i class="bi bi-cpu me-1"></i><?php echo htmlspecialchars($instance_id); ?></span></li>
            </ul>
        </div>
    </div>
</nav>
<div class="main-wrapper">
EOF

# -------------------------------------------------------------
# 3. FOOTER TEMPLATE (footer.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/footer.php
</div>
<footer>
    <div class="container">
        <div class="row g-4 text-secondary">
            <div class="col-md-5">
                <h6 class="fw-bold text-dark mb-2">Campus Shuttle Bus Ticketing</h6>
                <p class="small mb-0">Book your seat on a campus shuttle route ahead of time or view past reservation records.</p>
            </div>
            <div class="col-md-3 offset-md-1">
                <h6 class="fw-bold text-dark mb-2">EXPLORE</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="index.php" class="text-decoration-none text-secondary">Home</a></li>
                    <li><a href="schedule.php" class="text-decoration-none text-secondary">Schedule</a></li>
                    <li><a href="book.php" class="text-decoration-none text-secondary">Book Ticket</a></li>
                    <li><a href="history.php" class="text-decoration-none text-secondary">Booking History</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold text-dark mb-2">CONTACT</h6>
                <p class="small mb-0">Campus Transport Office<br>Main Gate Building<br>+60 3-4145 0450</p>
            </div>
        </div>
        <hr class="my-3 opacity-25">
        <div class="text-center small text-muted">&copy; 2026 Campus Shuttle Bus Ticketing</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
EOF

# -------------------------------------------------------------
# 4. HOME PAGE (index.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/index.php
<?php
include 'header.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM routes WHERE route_name LIKE ? ORDER BY departure_time ASC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $routes_result = $stmt->get_result();
} else {
    $routes_result = $conn->query("SELECT * FROM routes ORDER BY departure_time ASC");
}
?>

<div class="container mt-4">
    <div class="hero-card">
        <h1 class="fw-bold mb-2">Campus Shuttle Bus Ticketing</h1>
        <p class="lead mb-0">Book your seat on a campus shuttle route ahead of time.</p>
    </div>

    <div class="mb-4" id="routes">
        <h5 class="fw-bold mb-3"><span class="text-primary">•</span> Available Routes</h5>
        <div class="card border-0 shadow-sm p-3 mb-4 rounded-3">
            <form method="GET" action="index.php" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Route name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-tarumt w-100">Search</button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if ($routes_result && $routes_result->num_rows > 0): ?>
                <?php while($route = $routes_result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card-route h-100 d-flex flex-column">
                            <img src="<?php echo htmlspecialchars($route['image_url']); ?>" alt="<?php echo htmlspecialchars($route['route_name']); ?>">
                            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($route['route_name']); ?></h6>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($route['origin'] . ' → ' . $route['destination']); ?></p>
                                    <p class="small text-secondary mb-3">
                                        Departs <?php echo date('H:i', strtotime($route['departure_time'])); ?> · RM<?php echo number_format($route['price'], 2); ?> · <?php echo $route['available_seats']; ?> seats left
                                    </p>
                                </div>
                                <a href="book.php?route_id=<?php echo $route['id']; ?>" class="btn btn-tarumt w-100 text-center text-decoration-none">Book Ticket</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
EOF

# -------------------------------------------------------------
# 5. BOOKING PAGE (book.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/book.php
<?php
include 'header.php';
$message = '';
$selected_route_id = isset($_GET['route_id']) ? intval($_GET['route_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $route_id = intval($_POST['route_id']);
    $travel_date = trim($_POST['travel_date']);
    $tickets_booked = intval($_POST['tickets_booked']);
    $passenger_name = trim($_POST['passenger_name']);
    $passenger_email = trim($_POST['passenger_email']);

    if ($route_id > 0 && !empty($travel_date) && $tickets_booked > 0) {
        $stmt = $conn->prepare("SELECT price, available_seats FROM routes WHERE id = ?");
        $stmt->bind_param("i", $route_id);
        $stmt->execute();
        $route = $stmt->get_result()->fetch_assoc();

        if ($route && $route['available_seats'] >= $tickets_booked) {
            $total_price = $route['price'] * $tickets_booked;
            $booking_ref = 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

            $conn->begin_transaction();
            try {
                $insert = $conn->prepare("INSERT INTO bookings (booking_reference, route_id, passenger_name, passenger_email, tickets_booked, total_price, travel_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param("sissids", $booking_ref, $route_id, $passenger_name, $passenger_email, $tickets_booked, $total_price, $travel_date);
                $insert->execute();

                $update = $conn->prepare("UPDATE routes SET available_seats = available_seats - ? WHERE id = ?");
                $update->bind_param("ii", $tickets_booked, $route_id);
                $update->execute();

                $conn->commit();
                $message = "<div class='alert alert-success'>Booking Confirmed! Ref: <strong>{$booking_ref}</strong>. <a href='history.php' class='alert-link'>View History</a></div>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "<div class='alert alert-danger'>Error processing booking.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Insufficient seats available.</div>";
        }
    }
}
$routes = $conn->query("SELECT * FROM routes ORDER BY departure_time ASC");
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm p-4 rounded-3">
                <h4 class="fw-bold mb-4 text-center">Book a Shuttle Ticket</h4>
                <?php echo $message; ?>
                <form method="POST" action="book.php">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Route</label>
                        <select name="route_id" class="form-select py-2" required>
                            <?php while($r = $routes->fetch_assoc()): ?>
                                <option value="<?php echo $r['id']; ?>" <?php echo $selected_route_id == $r['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r['route_name']); ?> - RM<?php echo number_format($r['price'], 2); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="passenger_name" class="form-control py-2" placeholder="e.g. Alex Tan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Student Email</label>
                        <input type="email" name="passenger_email" class="form-control py-2" placeholder="student@tarc.edu.my" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Travel Date</label>
                        <input type="date" name="travel_date" class="form-control py-2" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Number of Seats</label>
                        <input type="number" name="tickets_booked" class="form-control py-2" min="1" max="5" value="1" required>
                    </div>
                    <button type="submit" name="submit_booking" class="btn btn-tarumt w-100 py-2 mb-2">Confirm Reservation</button>
                    <a href="index.php" class="btn btn-outline-secondary w-100 py-2">Back to Home</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
EOF

# -------------------------------------------------------------
# 6. SCHEDULE PAGE (schedule.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/schedule.php
<?php
include 'header.php';
$routes = $conn->query("SELECT * FROM routes ORDER BY departure_time ASC");
?>
<div class="container mt-4">
    <h3 class="fw-bold mb-1">Shuttle Timetable</h3>
    <p class="text-muted small mb-4">Daily departure schedule for all active campus shuttle routes.</p>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-uppercase text-muted small">
                        <th class="ps-4">Departs</th>
                        <th>Route</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Price (RM)</th>
                        <th>Seats Left</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $routes->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo date('H:i', strtotime($r['departure_time'])); ?></td>
                        <td class="fw-semibold text-primary"><?php echo htmlspecialchars($r['route_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['origin']); ?></td>
                        <td><?php echo htmlspecialchars($r['destination']); ?></td>
                        <td><?php echo number_format($r['price'], 2); ?></td>
                        <td><?php echo $r['available_seats']; ?></td>
                        <td class="pe-4 text-end">
                            <a href="book.php?route_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary px-3">Book Seat</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
EOF

# -------------------------------------------------------------
# 7. BOOKING HISTORY PAGE (history.php)
# -------------------------------------------------------------
cat << 'EOF' > /var/www/html/history.php
<?php
include 'header.php';

$query = "SELECT b.*, r.route_name, r.origin, r.destination 
          FROM bookings b 
          JOIN routes r ON b.route_id = r.id 
          ORDER BY b.booking_date DESC";
$history = $conn->query($query);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Booking History</h3>
            <p class="text-muted small mb-0">Record of reservations logged in the shuttle database system.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-uppercase text-muted small">
                        <th class="ps-4">Ref Code</th>
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
                            <td class="ps-4 font-monospace fw-bold text-primary"><?php echo htmlspecialchars($row['booking_reference']); ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($row['passenger_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['passenger_email']); ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($row['route_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['origin'] . ' → ' . $row['destination']); ?></div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['travel_date'])); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['tickets_booked']; ?> seat(s)</span></td>
                            <td class="fw-semibold">RM<?php echo number_format($row['total_price'], 2); ?></td>
                            <td class="pe-4 small text-muted"><?php echo date('Y-m-d H:i', strtotime($row['booking_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No booking history records found in database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
EOF

# -------------------------------------------------------------
# 8. DATABASE SCHEMA & SEED DATA (shuttle_db)
# -------------------------------------------------------------
mysql -h shuttle-db-instance.cxahvxc84ilj.us-east-1.rds.amazonaws.com -u admin -pTarumt2026Pass! << 'EOF'
CREATE DATABASE IF NOT EXISTS shuttle_db;
USE shuttle_db;

CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_time TIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available_seats INT NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    route_id INT NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_email VARCHAR(100) NOT NULL,
    tickets_booked INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    travel_date DATE NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- Seed Routes
TRUNCATE TABLE routes;
INSERT INTO routes (id, route_name, origin, destination, departure_time, price, available_seats, image_url) VALUES
(1, 'Campus - City Centre Express', 'Main Campus', 'City Centre', '08:00:00', 3.00, 40, 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&q=80'),
(2, 'Campus - LRT Shuttle', 'Main Campus', 'LRT Station', '09:30:00', 2.00, 30, 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=600&q=80'),
(3, 'Campus - Hostel Loop', 'Main Campus', 'Student Hostel', '17:30:00', 0.00, 25, 'https://images.unsplash.com/photo-1517649763962-0c623266010b?w=600&q=80');

-- Seed Initial Booking History
TRUNCATE TABLE bookings;
INSERT INTO bookings (booking_reference, route_id, passenger_name, passenger_email, tickets_booked, total_price, travel_date) VALUES
('TKT-8F2A190B', 1, 'Student User', 'student@tarc.edu.my', 2, 6.00, '2026-04-10'),
('TKT-1C9D4E2F', 2, 'Lee Wei Ming', 'leewm@tarc.edu.my', 1, 2.00, '2026-04-11'),
('TKT-7B3E5A8C', 3, 'Siti Nurhaliza', 'sitinur@tarc.edu.my', 1, 0.00, '2026-04-11');
EOF

chown -R apache:apache /var/www/html
chmod -R 755 /var/www/html
