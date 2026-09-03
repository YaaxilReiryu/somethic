-- =======================================================
-- SOMETHIC Handbag Retail Store Database Schema
-- Database: somethic
-- Target RDBMS: MySQL 8.0+ / MariaDB (XAMPP / phpMyAdmin / Railway)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `somethic` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `somethic`;

-- Disable foreign key checks during schema creation
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- 1. Table: users (Staff authentication)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'staff',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Table: products (Handbag catalog)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Table: reservations (Customer store pickup reservations)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `pickup_date` DATE NOT NULL,
  `pickup_time` VARCHAR(50) NOT NULL,
  `note` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
  `stock_deducted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reservations_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Table: inventory_tasks (Staff To-Do Checklist)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `inventory_tasks`;
CREATE TABLE `inventory_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. Table: staff_profiles (Store Manager / Staff Public Resume)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `staff_profiles`;
CREATE TABLE `staff_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `position` VARCHAR(100) NOT NULL,
  `bio` TEXT NOT NULL,
  `skills` TEXT NOT NULL,
  `experience` TEXT NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `profile_image` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_staff_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =======================================================
-- SEED DATA
-- =======================================================

-- 1. Insert Staff Account
-- Email: admin@somethic.com
-- Password: password (hashed securely using PHP password_hash)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Ariana Sofea', 'admin@somethic.com', '$2y$10$WpyOc6gl2eKuurNgY2afiO9OCQwZD.TyTNhoC823H3/SqsaMl1fRu', 'staff');

-- 2. Insert Staff Profile (1-to-1 with User 1)
INSERT INTO `staff_profiles` (`id`, `user_id`, `position`, `bio`, `skills`, `experience`, `phone`, `profile_image`) VALUES
(1, 1, 'SOMETHIC Store Manager', 'Ariana manages daily store operations and customer service at SOMETHIC. She has a keen eye for modern luxury, handbag ergonomics, and personalized client styling.', 'Customer Service, Retail Management, Product Styling, Inventory Management, Store Visuals', '3 years retail experience, 2 years handbag merchandising at premier boutique centers.', '+60 12-345 6789', 'assets/images/staff_ariana.jpg');

-- 3. Insert At Least 8 Realistic Handbag Products
INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `stock`, `image`) VALUES
(1, 'SOMETHIC Luna Shoulder Bag', 'Shoulder Bag', 'Sculpted with premium vegan calfskin leather and polished gold-tone hardware. Featuring a crescent silhouette that sits comfortably on the shoulder, the Luna bag elevates your day-to-night ensemble.', 249.00, 12, 'assets/images/bag_luna.svg'),
(2, 'SOMETHIC Mila Tote', 'Tote Bag', 'The quintessential everyday companion. Spacious enough for a 13-inch laptop, planner, and beauty essentials, complete with dual reinforced handles and a detachable interior zip pouch.', 319.00, 8, 'assets/images/bag_mila.svg'),
(3, 'SOMETHIC Ava Crossbody', 'Crossbody Bag', 'Compact yet surprisingly roomy, the Ava Crossbody is accented with our signature gold turn-lock closure and an adjustable leather strap for effortless hands-free styling.', 189.00, 15, 'assets/images/bag_ava.svg'),
(4, 'SOMETHIC Bella Mini Bag', 'Mini Bag', 'A chic micro-bag for evenings and weekend brunches. Crafted from smooth structured leather with an eye-catching top handle and metallic chain strap.', 159.00, 0, 'assets/images/bag_bella.svg'),
(5, 'SOMETHIC Sofia Handbag', 'Handbag', 'An architectural structured satchel with accordion side gussets and protective metal feet. Exudes professional sophistication for corporate and formal settings.', 289.00, 4, 'assets/images/bag_sofia.svg'),
(6, 'SOMETHIC Emma Shoulder Bag', 'Shoulder Bag', 'Minimalist baguette style inspired by 90s heritage fashion. Clean lines, magnetic flap closure, and a silky satin interior lining with an interior card slot.', 229.00, 9, 'assets/images/bag_emma.svg'),
(7, 'SOMETHIC Chloe Tote', 'Tote Bag', 'Soft grained leather shopper with relaxed drape and wide shoulder straps. Features water-resistant canvas lining and a secure magnetic clasp closure.', 349.00, 6, 'assets/images/bag_chloe.svg'),
(8, 'SOMETHIC Lily Crossbody', 'Crossbody Bag', 'Quilted geometric stitching with an interwoven gold chain strap. Designed with dual interior compartments to keep your phone, keys, and cards neatly organized.', 199.00, 14, 'assets/images/bag_lily.svg');

-- 4. Insert Inventory Checklist Tasks
INSERT INTO `inventory_tasks` (`id`, `task`, `description`, `status`) VALUES
(1, 'Check handbag stock', 'Verify physical display counts against inventory morning checklist', 'Completed'),
(2, 'Restock packaging', 'Assemble luxury gift boxes, dust bags, and ribbon spools at checkout counter', 'Pending'),
(3, 'Check damaged products', 'Inspect returned items and display units for hardware scuffs or stitching faults', 'Pending'),
(4, 'Arrange handbag display', 'Rearrange front pedestal showcase featuring the new Luna Shoulder Bag series', 'Completed'),
(5, 'Update product labels', 'Print and insert updated RM promotional price tags for weekend trunk showcase', 'Pending'),
(6, 'Clean display shelves', 'Wipe acrylic display shelves and polish brass logo fixtures', 'Pending');

-- 5. Insert Sample Reservations
INSERT INTO `reservations` (`id`, `customer_name`, `phone`, `email`, `product_id`, `quantity`, `pickup_date`, `pickup_time`, `note`, `status`, `stock_deducted`) VALUES
(1, 'Nurul Huda', '+60 17-987 6543', 'nurul.huda@example.com', 1, 1, CURDATE() + INTERVAL 1 DAY, '2:00 PM - 4:00 PM', 'Please prepare gift wrapping with ribbon if available.', 'Pending', 0),
(2, 'Sarah Tan', '+60 19-332 1100', 'sarah.tan@example.com', 2, 1, CURDATE(), '10:00 AM - 12:00 PM', 'Will come by during lunch hour.', 'Confirmed', 0);
