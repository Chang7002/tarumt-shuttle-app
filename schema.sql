CREATE DATABASE IF NOT EXISTS shuttle_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shuttle_db;

-- 1. Routes Table
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_time TIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    available_seats INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departure (departure_time),
    INDEX idx_route_search (route_name, origin, destination)
) ENGINE=InnoDB;

-- 2. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_email (email)
) ENGINE=InnoDB;

-- 3. Bookings Table (Aligned with PHP endpoints)
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    route_id INT NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    seats INT NOT NULL CHECK (seats > 0),
    total_price DECIMAL(10, 2) NOT NULL,
    travel_date DATE NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_routes FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE RESTRICT,
    INDEX idx_passenger_email (email),
    INDEX idx_booking_ref (booking_reference)
) ENGINE=InnoDB;

-- Default Admin Account (Password: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('System Admin', 'admin@tarumt.edu.my', '$2y$10$e.w2K210L9sNsk0Vv7w4v.XnJ5Ew/QZq3yETh1K3V6I1L9x9aQ42S', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Seed Routes
INSERT INTO routes (id, route_name, origin, destination, departure_time, price, available_seats, image_url) VALUES
(1, 'Campus - City Centre Express', 'Main Campus', 'City Centre', '08:00:00', 3.00, 40, 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800&q=80'),
(2, 'Campus - LRT Shuttle', 'Main Campus', 'LRT Station', '09:30:00', 2.00, 30, 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=800&q=80'),
(3, 'Campus - Hostel Loop', 'Main Campus', 'Student Hostel', '17:30:00', 0.00, 25, 'https://images.unsplash.com/photo-1517649763962-0c623266010b?w=800&q=80')
ON DUPLICATE KEY UPDATE route_name=VALUES(route_name);
