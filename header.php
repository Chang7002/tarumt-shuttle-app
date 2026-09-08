<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$node_info = get_ec2_metadata();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAR UMT Campus Shuttle Ticketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --tarumt-blue: #0d6efd; --tarumt-dark: #0a58ca; }
        body { background-color: #f4f6f9; min-height: 100vh; display: flex; flex-direction: column; }
        .main-content { flex: 1; }
        .node-info-badge { font-size: 0.75rem; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-bus-front-fill me-2"></i>TAR UMT Shuttle
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= $current_page==='index.php'?'active':'' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $current_page==='schedule.php'?'active':'' ?>" href="schedule.php">Schedule</a></li>
                <li class="nav-item"><a class="nav-link <?= $current_page==='history.php'?'active':'' ?>" href="history.php">Booking History</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="book.php" class="btn btn-light text-primary fw-semibold btn-sm">Book Ticket</a>
                <span class="badge rounded-pill node-info-badge text-white px-3 py-2">
                    <i class="bi bi-cpu me-1"></i><?= e($node_info['instance_id']) ?> (<?= e($node_info['az']) ?>)
                </span>
            </div>
        </div>
    </div>
</nav>
<div class="main-content py-4">