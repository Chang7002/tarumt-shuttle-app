</div> <!-- Close .main-content -->

<footer class="bg-white border-top py-4 mt-auto">
    <div class="container">
        <div class="row g-4 text-secondary">
            <div class="col-md-5">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="bi bi-bus-front-fill me-1 text-primary"></i>TAR UMT Shuttle System
                </h6>
                <p class="small mb-0">
                    Automated Ticketing & Dynamic Load Balancing Platform running on AWS EC2 & RDS.
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold text-dark mb-2">Navigation</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="index.php" class="text-decoration-none text-secondary">Home</a></li>
                    <li><a href="schedule.php" class="text-decoration-none text-secondary">Schedule</a></li>
                    <li><a href="book.php" class="text-decoration-none text-secondary">Book Ticket</a></li>
                    <li><a href="history.php" class="text-decoration-none text-secondary">Booking History</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold text-dark mb-2">Active Node</h6>
                <p class="small mb-0">
                    Instance: <code class="text-primary"><?= e($node_info['instance_id'] ?? 'N/A') ?></code><br>
                    Zone: <code class="text-primary"><?= e($node_info['az'] ?? 'N/A') ?></code>
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold text-dark mb-2">Support</h6>
                <p class="small mb-0">Campus Transport Office<br>Main Gate Building<br>+60 3-4145 0450</p>
            </div>
        </div>
        <hr class="my-3 opacity-25">
        <div class="text-center small text-muted">&copy; <?= date('Y') ?> TAR UMT Campus Transport System</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
