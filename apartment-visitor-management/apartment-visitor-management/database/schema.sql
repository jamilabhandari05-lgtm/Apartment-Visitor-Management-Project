-- Apartment Visitor Management (GateWatch)
-- Import via phpMyAdmin or: mysql -u root -P 3306 < schema.sql
-- Creates database, schema, and demo seed data.
-- Date-relative seed rows use NOW()/CURDATE() so the "currently inside" /
-- overstay demo state always looks fresh, whenever this file is imported.

CREATE DATABASE IF NOT EXISTS apartment_visitor_management
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE apartment_visitor_management;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS visitor_logs;
DROP TABLE IF EXISTS gate_passes;
DROP TABLE IF EXISTS residents;
DROP TABLE IF EXISTS flats;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Schema
-- ============================================================

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('super_admin','guard','resident') NOT NULL,
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE flats (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    block         VARCHAR(10) NOT NULL,
    number        VARCHAR(10) NOT NULL,
    floor         INT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_block_number (block, number)
) ENGINE=InnoDB;

CREATE TABLE residents (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL UNIQUE,
    flat_id       INT NOT NULL,
    phone         VARCHAR(20) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flat_id) REFERENCES flats(id),
    KEY idx_residents_flat (flat_id)
) ENGINE=InnoDB;

CREATE TABLE gate_passes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    resident_id       INT NOT NULL,
    visitor_name      VARCHAR(100) NOT NULL,
    visitor_phone     VARCHAR(20) NULL,
    purpose           VARCHAR(255) NULL,
    expected_start    DATETIME NOT NULL,
    expected_end      DATETIME NOT NULL,
    code              VARCHAR(10) NOT NULL UNIQUE,
    status            ENUM('pending','used','cancelled') NOT NULL DEFAULT 'pending',
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    KEY idx_gate_passes_resident (resident_id),
    KEY idx_gate_passes_status (status)
) ENGINE=InnoDB;

CREATE TABLE visitor_logs (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    gate_pass_id      INT NULL,
    flat_id           INT NOT NULL,
    visitor_name      VARCHAR(100) NOT NULL,
    visitor_phone     VARCHAR(20) NULL,
    purpose           VARCHAR(255) NULL,
    photo_note        VARCHAR(255) NULL,
    checked_in_at     DATETIME NOT NULL,
    checked_in_by     INT NOT NULL,
    checked_out_at    DATETIME NULL,
    checked_out_by    INT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gate_pass_id) REFERENCES gate_passes(id) ON DELETE SET NULL,
    FOREIGN KEY (flat_id) REFERENCES flats(id),
    FOREIGN KEY (checked_in_by) REFERENCES users(id),
    FOREIGN KEY (checked_out_by) REFERENCES users(id),
    KEY idx_visitor_logs_flat (flat_id),
    KEY idx_visitor_logs_checked_out (checked_out_at),
    KEY idx_visitor_logs_checked_in_at (checked_in_at)
) ENGINE=InnoDB;

-- ============================================================
-- Seed data
-- ============================================================

-- Passwords: Admin@123 / Guard@123 / Resident@123 (bcrypt via password_hash)
INSERT INTO users (id, name, email, password, role, status) VALUES
(1,  'Ananya Krishnan', 'admin@greenwood.test',    '$2y$12$Fy3pbTaXzS36zI11Hp2Q6uGU7OQakEj9SuCasv/oj/kdvQMg1Zt8u', 'super_admin', 'active'),
(2,  'Ramesh Yadav',    'guard1@greenwood.test',   '$2y$12$RD6IfDRMK3zs5VYqKEVAj.TWqo116OILPTYjSqWXaaHbHmXW60a52', 'guard',       'active'),
(3,  'Suresh Pillai',   'guard2@greenwood.test',   '$2y$12$RD6IfDRMK3zs5VYqKEVAj.TWqo116OILPTYjSqWXaaHbHmXW60a52', 'guard',       'active'),
(4,  'Vikram Singh',    'guard3@greenwood.test',   '$2y$12$RD6IfDRMK3zs5VYqKEVAj.TWqo116OILPTYjSqWXaaHbHmXW60a52', 'guard',       'active'),
(5,  'Rohit Sharma',    'resident1@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(6,  'Neha Kapoor',     'resident2@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(7,  'Arjun Nair',      'resident3@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(8,  'Divya Menon',     'resident4@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(9,  'Karan Malhotra',  'resident5@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(10, 'Pooja Reddy',     'resident6@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(11, 'Sanjay Iyer',     'resident7@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(12, 'Meera Joshi',     'resident8@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(13, 'Aditya Verma',    'resident9@greenwood.test',  '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(14, 'Sneha Pillai',    'resident10@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(15, 'Vivek Rao',       'resident11@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(16, 'Anjali Desai',    'resident12@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(17, 'Manoj Kumar',     'resident13@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(18, 'Kavita Bhat',     'resident14@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(19, 'Rahul Kulkarni',  'resident15@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(20, 'Shalini Gupta',   'resident16@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(21, 'Deepak Chauhan',  'resident17@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(22, 'Ritu Agarwal',    'resident18@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(23, 'Farhan Sheikh',   'resident19@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'active'),
(24, 'Lakshmi Narayan', 'resident20@greenwood.test', '$2y$12$KzDx0AaZEsNWklu3lRU7fOeL0GRnsHACShk.AqB1dbf37uI2em6aC', 'resident', 'inactive');

INSERT INTO flats (id, block, number, floor) VALUES
(1,  'A', '101', 1), (2,  'A', '102', 1), (3,  'A', '103', 1), (4,  'A', '104', 1), (5,  'A', '105', 1),
(6,  'B', '201', 2), (7,  'B', '202', 2), (8,  'B', '203', 2), (9,  'B', '204', 2), (10, 'B', '205', 2),
(11, 'C', '301', 3), (12, 'C', '302', 3), (13, 'C', '303', 3), (14, 'C', '304', 3), (15, 'C', '305', 3),
(16, 'D', '401', 4), (17, 'D', '402', 4), (18, 'D', '403', 4), (19, 'D', '404', 4), (20, 'D', '405', 4);

INSERT INTO residents (id, user_id, flat_id, phone) VALUES
(1,  5,  1,  '9810000001'), (2,  6,  2,  '9810000002'), (3,  7,  3,  '9810000003'), (4,  8,  4,  '9810000004'),
(5,  9,  5,  '9810000005'), (6,  10, 6,  '9810000006'), (7,  11, 7,  '9810000007'), (8,  12, 8,  '9810000008'),
(9,  13, 9,  '9810000009'), (10, 14, 10, '9810000010'), (11, 15, 11, '9810000011'), (12, 16, 12, '9810000012'),
(13, 17, 13, '9810000013'), (14, 18, 14, '9810000014'), (15, 19, 15, '9810000015'), (16, 20, 16, '9810000016'),
(17, 21, 17, '9810000017'), (18, 22, 18, '9810000018'), (19, 23, 19, '9810000019'), (20, 24, 20, '9810000020');

-- Gate passes: 1-8 already used (checked in, see visitor_logs below), 9-13 upcoming/pending,
-- 14 pending but its window has already lapsed (shows as "Expired" in the UI, computed, not stored),
-- 15-16 cancelled, 17-20 pending for a later date.
INSERT INTO gate_passes (id, resident_id, visitor_name, visitor_phone, purpose, expected_start, expected_end, code, status) VALUES
(1,  1,  'Ravi Deshmukh',    '9900011101', 'Personal visit',   DATE_SUB(NOW(), INTERVAL 49 HOUR), DATE_SUB(NOW(), INTERVAL 46 HOUR), 'K3F7Q', 'used'),
(2,  2,  'Sunita Agnihotri', '9900011102', 'Family guest',     DATE_SUB(NOW(), INTERVAL 1 HOUR),  DATE_ADD(NOW(), INTERVAL 2 HOUR),  'M9X2L', 'used'),
(3,  3,  'Ajay Bansal',      '9900011103', 'Friend visiting',  DATE_SUB(NOW(), INTERVAL 5 HOUR),  DATE_SUB(NOW(), INTERVAL 2 HOUR),  'P4W8R', 'used'),
(4,  4,  'Kiran Thomas',     '9900011104', 'Relative visit',   DATE_SUB(NOW(), INTERVAL 72 HOUR), DATE_SUB(NOW(), INTERVAL 70 HOUR), 'Q7Z1N', 'used'),
(5,  5,  'Nikhil Chopra',    '9900011105', 'Personal visit',   DATE_SUB(NOW(), INTERVAL 120 HOUR),DATE_SUB(NOW(), INTERVAL 117 HOUR),'T2Y6B', 'used'),
(6,  6,  'Geeta Subramaniam','9900011106', 'Family guest',     DATE_SUB(NOW(), INTERVAL 168 HOUR),DATE_SUB(NOW(), INTERVAL 165 HOUR),'V8H3D', 'used'),
(7,  7,  'Mohit Saxena',     '9900011107', 'Friend visiting',  DATE_SUB(NOW(), INTERVAL 30 HOUR), DATE_SUB(NOW(), INTERVAL 27 HOUR), 'W1J9C', 'used'),
(8,  8,  'Preeti Rawat',     '9900011108', 'Personal visit',   DATE_SUB(NOW(), INTERVAL 9 HOUR),  DATE_SUB(NOW(), INTERVAL 6 HOUR),  'X5K4E', 'used'),
(9,  9,  'Alok Mathur',      '9900011109', 'Personal visit',   DATE_ADD(NOW(), INTERVAL 3 HOUR),  DATE_ADD(NOW(), INTERVAL 5 HOUR),  'Y6L2F', 'pending'),
(10, 10, 'Rekha Chandran',   '9900011110', 'Family guest',     DATE_ADD(NOW(), INTERVAL 1 DAY),   DATE_ADD(NOW(), INTERVAL 1 DAY),   'Z3M8G', 'pending'),
(11, 11, 'Harsh Vardhan',    '9900011111', 'Friend visiting',  DATE_ADD(NOW(), INTERVAL 2 DAY),   DATE_ADD(NOW(), INTERVAL 2 DAY),   'A9N4H', 'pending'),
(12, 12, 'Ismita Sen',       '9900011112', 'Personal visit',   DATE_ADD(NOW(), INTERVAL 6 HOUR),  DATE_ADD(NOW(), INTERVAL 8 HOUR),  'B2P7J', 'pending'),
(13, 13, 'Yash Trivedi',     '9900011113', 'Relative visit',   DATE_ADD(NOW(), INTERVAL 30 HOUR), DATE_ADD(NOW(), INTERVAL 32 HOUR), 'C6Q1K', 'pending'),
(14, 14, 'Om Prakash',       '9900011114', 'Personal visit',   DATE_SUB(NOW(), INTERVAL 26 HOUR), DATE_SUB(NOW(), INTERVAL 24 HOUR), 'D4R9L', 'pending'),
(15, 15, 'Tara Balan',       '9900011115', 'Family guest',     DATE_ADD(NOW(), INTERVAL 4 HOUR),  DATE_ADD(NOW(), INTERVAL 6 HOUR),  'E7S3M', 'cancelled'),
(16, 1,  'Ravi Deshmukh',    '9900011101', 'Personal visit',   DATE_ADD(NOW(), INTERVAL 5 DAY),   DATE_ADD(NOW(), INTERVAL 5 DAY),   'F1T6N', 'cancelled'),
(17, 16, 'Zubin Irani',      '9900011116', 'Friend visiting',  DATE_ADD(NOW(), INTERVAL 3 DAY),   DATE_ADD(NOW(), INTERVAL 3 DAY),   'G8U2P', 'pending'),
(18, 17, 'Nandini Kohli',    '9900011117', 'Personal visit',   DATE_ADD(NOW(), INTERVAL 12 HOUR), DATE_ADD(NOW(), INTERVAL 14 HOUR), 'H5V7Q', 'pending'),
(19, 18, 'Rajeev Menon',     '9900011118', 'Relative visit',   DATE_ADD(NOW(), INTERVAL 4 DAY),   DATE_ADD(NOW(), INTERVAL 4 DAY),   'J3W4R', 'pending'),
(20, 19, 'Simran Kaur',      '9900011119', 'Family guest',     DATE_ADD(NOW(), INTERVAL 8 HOUR),  DATE_ADD(NOW(), INTERVAL 10 HOUR), 'K9X8S', 'pending');

-- Visitor logs: rows 1-8 correspond to the 8 "used" gate passes above (checked in on arrival,
-- most already checked out; #2 is still inside within its window, #3 is still inside past its
-- window so it reads as overstaying). Rows 9-25 are guard-logged walk-ins (no gate pass) --
-- deliveries, cabs, maintenance -- including a couple currently inside (one within the default
-- walk-in max-stay, one past it so it shows overstaying too).
INSERT INTO visitor_logs (id, gate_pass_id, flat_id, visitor_name, visitor_phone, purpose, photo_note, checked_in_at, checked_in_by, checked_out_at, checked_out_by) VALUES
(1,  1,  1,  'Ravi Deshmukh',      '9900011101', 'Personal visit',  'Arrived by auto',                DATE_SUB(NOW(), INTERVAL 49 HOUR), 2, DATE_SUB(NOW(), INTERVAL 47 HOUR), 2),
(2,  2,  2,  'Sunita Agnihotri',   '9900011102', 'Family guest',    'Arrived on foot',                 DATE_SUB(NOW(), INTERVAL 50 MINUTE), 3, NULL, NULL),
(3,  3,  3,  'Ajay Bansal',        '9900011103', 'Friend visiting', 'Blue sedan, KA-05-CD-4521',        DATE_SUB(NOW(), INTERVAL 5 HOUR), 2, NULL, NULL),
(4,  4,  4,  'Kiran Thomas',       '9900011104', 'Relative visit',  'Arrived by cab',                   DATE_SUB(NOW(), INTERVAL 72 HOUR), 4, DATE_SUB(NOW(), INTERVAL 69 HOUR), 4),
(5,  5,  5,  'Nikhil Chopra',      '9900011105', 'Personal visit',  'On bike, KA-01-EF-7789',           DATE_SUB(NOW(), INTERVAL 120 HOUR), 3, DATE_SUB(NOW(), INTERVAL 117 HOUR), 3),
(6,  6,  6,  'Geeta Subramaniam',  '9900011106', 'Family guest',    'Arrived by auto',                  DATE_SUB(NOW(), INTERVAL 168 HOUR), 2, DATE_SUB(NOW(), INTERVAL 165 HOUR), 2),
(7,  7,  7,  'Mohit Saxena',       '9900011107', 'Friend visiting', 'Arrived on foot',                  DATE_SUB(NOW(), INTERVAL 30 HOUR), 4, DATE_SUB(NOW(), INTERVAL 27 HOUR), 4),
(8,  8,  8,  'Preeti Rawat',       '9900011108', 'Personal visit',  'Arrived by cab',                   DATE_SUB(NOW(), INTERVAL 9 HOUR), 3, DATE_SUB(NOW(), INTERVAL 6 HOUR), 3),
(9,  NULL, 9,  'Amazon Delivery',    '9911100001', 'Delivery',        'Package for B-204',                DATE_SUB(NOW(), INTERVAL 30 MINUTE), 2, NULL, NULL),
(10, NULL, 11, 'Zomato Delivery',    '9911100002', 'Delivery',        'Food order',                        DATE_SUB(NOW(), INTERVAL 6 HOUR), 3, DATE_SUB(NOW(), INTERVAL 350 MINUTE), 3),
(11, NULL, 1,  'BlueDart Courier',   '9911100003', 'Delivery',        'Documents',                         DATE_SUB(NOW(), INTERVAL 8 DAY), 2, DATE_SUB(NOW(), INTERVAL 8 DAY), 2),
(12, NULL, 6,  'BlueDart Courier',   '9911100003', 'Delivery',        'Small parcel',                      DATE_SUB(NOW(), INTERVAL 6 DAY), 4, DATE_SUB(NOW(), INTERVAL 6 DAY), 4),
(13, NULL, 11, 'BlueDart Courier',   '9911100003', 'Delivery',        'Parcel for C-301',                  DATE_SUB(NOW(), INTERVAL 3 DAY), 3, DATE_SUB(NOW(), INTERVAL 3 DAY), 3),
(14, NULL, 16, 'Urban Company Plumber','9911100004','Maintenance',    'Plumbing repair, ID checked',       DATE_SUB(NOW(), INTERVAL 4 DAY), 2, DATE_SUB(NOW(), INTERVAL 4 DAY), 2),
(15, NULL, 2,  'Ola Cab Driver',     '9911100005', 'Cab pickup',      'White Swift, KA-03-GH-1122',        DATE_SUB(NOW(), INTERVAL 5 DAY), 4, DATE_SUB(NOW(), INTERVAL 5 DAY), 4),
(16, NULL, 12, 'Swiggy Delivery',    '9911100006', 'Delivery',        'Food order',                        DATE_SUB(NOW(), INTERVAL 2 DAY), 3, DATE_SUB(NOW(), INTERVAL 2 DAY), 3),
(17, NULL, 17, 'Swiggy Delivery',    '9911100006', 'Delivery',        'Food order',                        DATE_SUB(NOW(), INTERVAL 1 DAY), 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 2),
(18, NULL, 7,  'Piped Gas Technician','9911100007','Maintenance',    'Cylinder check',                    DATE_SUB(NOW(), INTERVAL 9 DAY), 4, DATE_SUB(NOW(), INTERVAL 9 DAY), 4),
(19, NULL, 13, 'Uber Cab Driver',    '9911100008', 'Cab pickup',      'Grey Dzire, KA-04-JK-3345',         DATE_SUB(NOW(), INTERVAL 7 DAY), 3, DATE_SUB(NOW(), INTERVAL 7 DAY), 3),
(20, NULL, 18, 'RO Water Technician','9911100009', 'Maintenance',     'Filter service',                    DATE_SUB(NOW(), INTERVAL 10 DAY), 2, DATE_SUB(NOW(), INTERVAL 10 DAY), 2),
(21, NULL, 3,  'Flipkart Delivery',  '9911100010', 'Delivery',        'Package for A-103',                 DATE_SUB(NOW(), INTERVAL 11 HOUR), 3, DATE_SUB(NOW(), INTERVAL 10 HOUR), 3),
(22, NULL, 19, 'Amazon Delivery',    '9911100001', 'Delivery',        'Package for D-404',                 DATE_SUB(NOW(), INTERVAL 2 DAY), 4, DATE_SUB(NOW(), INTERVAL 2 DAY), 4),
(23, NULL, 20, 'Electrician - Ravi', '9911100011', 'Maintenance',     'Fan installation',                  DATE_SUB(NOW(), INTERVAL 390 MINUTE), 2, DATE_SUB(NOW(), INTERVAL 5 HOUR), 2),
(24, NULL, 14, 'Unregistered Guest', '9911100012', 'Unannounced guest','Says visiting Verma family',       DATE_SUB(NOW(), INTERVAL 6 HOUR), 3, NULL, NULL),
(25, NULL, 8,  'Milk Delivery Boy',  '9911100013', 'Delivery',        'Daily milk delivery',               DATE_SUB(NOW(), INTERVAL 10 MINUTE), 4, NULL, NULL);
