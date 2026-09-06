<?php
// TEMPORARY DEBUG SCRIPT — delete after use

echo "<h3>1. Extension check</h3>";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "<br>";
echo "pdo_sqlite loaded: " . (extension_loaded('pdo_sqlite') ? 'YES' : 'NO') . "<br>";

echo "<h3>2. Environment variables seen by PHP</h3>";
echo "MYSQLHOST: " . getenv('MYSQLHOST') . "<br>";
echo "MYSQLPORT: " . getenv('MYSQLPORT') . "<br>";
echo "MYSQLDATABASE: " . getenv('MYSQLDATABASE') . "<br>";
echo "MYSQLUSER: " . getenv('MYSQLUSER') . "<br>";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? '(set, hidden)' : '(NOT SET)') . "<br>";

echo "<h3>3. Manual connection attempt</h3>";
if (extension_loaded('pdo_mysql')) {
    $db_host = getenv('MYSQLHOST') ?: 'localhost';
    $db_port = getenv('MYSQLPORT') ?: '3306';
    $db_name = getenv('MYSQLDATABASE') ?: 'somethic';
    $db_user = getenv('MYSQLUSER') ?: 'root';
    $db_pass = getenv('MYSQLPASSWORD') ?: '';

    try {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "SUCCESS: Connected to MySQL directly!<br>";
    } catch (PDOException $e) {
        echo "FAILED: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
} else {
    echo "Skipped — pdo_mysql extension not loaded.<br>";
}
