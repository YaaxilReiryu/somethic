<?php
/**
 * SOMETHIC - Database Connection Configuration
 * Pure PHP + PDO Database Handler
 * 
 * Supports:
 * 1. Local XAMPP MySQL (Host: localhost, Database: somethic, User: root, Pass: '')
 * 2. Cloud deployment (Railway / Custom Environment Variables)
 * 3. Graceful fallback for local development environments
 */

// Retrieve database configuration from environment variables or use local XAMPP defaults
$db_host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
$db_name = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'somethic';
$db_user = getenv('DB_USERNAME') ?: getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');

$pdo = null;

// Try MySQL connection first
if (extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        $pdo = null; // MySQL connection failed, fall through to fallback
    }
}

// Fallback to local SQLite storage for sandboxed live previews or offline testing
if (!$pdo && extension_loaded('pdo_sqlite')) {
    $sqlite_dir = __DIR__;
    $sqlite_file = $sqlite_dir . '/somethic.sqlite';
    $is_fresh = !file_exists($sqlite_file);

    try {
        $pdo = new PDO("sqlite:" . $sqlite_file, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        if ($is_fresh || filesize($sqlite_file) === 0) {
            init_somethic_sqlite($pdo);
        }
    } catch (PDOException $e) {
        die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
    }
}

if (!$pdo) {
    die("Database Connection Failed. Please ensure MySQL is running or SQLite PDO extension is enabled.");
}

/**
 * Helper function to seed SQLite database for instant live preview capability
 */
function init_somethic_sqlite(PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'staff',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            category VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            image VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS reservations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name VARCHAR(100) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(100) NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            pickup_date DATE NOT NULL,
            pickup_time VARCHAR(50) NOT NULL,
            note TEXT NULL,
            status VARCHAR(50) DEFAULT 'Pending',
            stock_deducted INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS inventory_tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            task VARCHAR(255) NOT NULL,
            description TEXT NULL,
            status VARCHAR(50) DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS staff_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            position VARCHAR(100) NOT NULL,
            bio TEXT NOT NULL,
            skills TEXT NOT NULL,
            experience TEXT NOT NULL,
            phone VARCHAR(50) NOT NULL,
            profile_image VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    // Insert staff user
    $hashed_pw = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Ariana Sofea', 'admin@somethic.com', $hashed_pw, 'staff']);
    $user_id = $db->lastInsertId();

    // Insert staff profile
    $stmt = $db->prepare("INSERT INTO staff_profiles (user_id, position, bio, skills, experience, phone, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        'SOMETHIC Store Manager',
        'Ariana manages daily store operations, customer reservations, and visual merchandising at SOMETHIC. She has a keen eye for modern luxury and personalized client styling.',
        'Customer Service, Retail Management, Product Styling, Inventory Management, Store Visuals',
        '3 years retail management experience, 2 years handbag merchandising at premier boutique centers.',
        '+60 12-345 6789',
        'assets/images/staff_ariana.jpg'
    ]);

    // Insert 8 sample products
    $products = [
        [
            'SOMETHIC Luna Shoulder Bag',
            'Shoulder Bag',
            'Sculpted with premium vegan calfskin leather and polished gold-tone hardware. Featuring a crescent silhouette that sits comfortably on the shoulder, the Luna bag elevates your day-to-night ensemble.',
            249.00,
            12,
            'assets/images/bag_luna.svg'
        ],
        [
            'SOMETHIC Mila Tote',
            'Tote Bag',
            'The quintessential everyday companion. Spacious enough for a 13-inch laptop, planner, and beauty essentials, complete with dual reinforced handles and a detachable interior zip pouch.',
            319.00,
            8,
            'assets/images/bag_mila.svg'
        ],
        [
            'SOMETHIC Ava Crossbody',
            'Crossbody Bag',
            'Compact yet surprisingly roomy, the Ava Crossbody is accented with our signature gold turn-lock closure and an adjustable leather strap for effortless hands-free styling.',
            189.00,
            15,
            'assets/images/bag_ava.svg'
        ],
        [
            'SOMETHIC Bella Mini Bag',
            'Mini Bag',
            'A chic micro-bag for evenings and weekend brunches. Crafted from smooth structured leather with an eye-catching top handle and metallic chain strap.',
            159.00,
            0, // Out of stock example
            'assets/images/bag_bella.svg'
        ],
        [
            'SOMETHIC Sofia Handbag',
            'Handbag',
            'An architectural structured satchel with accordion side gussets and protective metal feet. Exudes professional sophistication for corporate and formal settings.',
            289.00,
            4, // Low stock example
            'assets/images/bag_sofia.svg'
        ],
        [
            'SOMETHIC Emma Shoulder Bag',
            'Shoulder Bag',
            'Minimalist baguette style inspired by 90s heritage fashion. Clean lines, magnetic flap closure, and a silky satin interior lining with an interior card slot.',
            229.00,
            9,
            'assets/images/bag_emma.svg'
        ],
        [
            'SOMETHIC Chloe Tote',
            'Tote Bag',
            'Soft grained leather shopper with relaxed drape and wide shoulder straps. Features water-resistant canvas lining and a secure magnetic clasp closure.',
            349.00,
            6,
            'assets/images/bag_chloe.svg'
        ],
        [
            'SOMETHIC Lily Crossbody',
            'Crossbody Bag',
            'Quilted geometric stitching with an interwoven gold chain strap. Designed with dual interior compartments to keep your phone, keys, and cards neatly organized.',
            199.00,
            14,
            'assets/images/bag_lily.svg'
        ]
    ];

    $stmt = $db->prepare("INSERT INTO products (name, category, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }

    // Insert sample inventory tasks
    $tasks = [
        ['Check handbag stock', 'Verify display stock counts against morning inventory checklist', 'Completed'],
        ['Restock packaging', 'Assemble luxury gift boxes, dust bags, and ribbon spools at checkout counter', 'Pending'],
        ['Check damaged products', 'Inspect returned items and display units for hardware scuffs or stitching faults', 'Pending'],
        ['Arrange handbag display', 'Rearrange front pedestal showcase featuring the new Luna Shoulder Bag series', 'Completed'],
        ['Update product labels', 'Print and insert updated RM promotional price tags for weekend trunk showcase', 'Pending'],
        ['Clean display shelves', 'Wipe acrylic display shelves and polish brass logo fixtures', 'Pending']
    ];

    $stmt = $db->prepare("INSERT INTO inventory_tasks (task, description, status) VALUES (?, ?, ?)");
    foreach ($tasks as $t) {
        $stmt->execute($t);
    }

    // Insert 2 sample reservations for demonstration
    $reservations = [
        [
            'Nurul Huda',
            '+60 17-987 6543',
            'nurul.huda@example.com',
            1, // Luna bag
            1,
            date('Y-m-d', strtotime('+1 day')),
            '2:00 PM - 4:00 PM',
            'Please prepare gift wrapping with ribbon if available.',
            'Pending',
            0
        ],
        [
            'Sarah Tan',
            '+60 19-332 1100',
            'sarah.tan@example.com',
            2, // Mila tote
            1,
            date('Y-m-d'),
            '10:00 AM - 12:00 PM',
            'Will come by during lunch hour.',
            'Confirmed',
            0
        ]
    ];

    $stmt = $db->prepare("INSERT INTO reservations (customer_name, phone, email, product_id, quantity, pickup_date, pickup_time, note, status, stock_deducted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($reservations as $r) {
        $stmt->execute($r);
    }
}
