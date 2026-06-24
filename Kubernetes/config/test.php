<?php
include 'database.php';

echo "=== Database Connection Test ===\n";

// Test connection
if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "\n";
} else {
    echo "✅ Database connected successfully!\n";
    
    // Test a simple query
    $result = fetchAll("SELECT * FROM users LIMIT 1");
    echo "✅ Query executed!\n";
    echo "Users in database: " . count($result) . "\n";
    
    // List all tables
    $tables = fetchAll("SHOW TABLES");
    echo "✅ Tables in database:\n";
    foreach ($tables as $table) {
        echo "   - " . array_values($table)[0] . "\n";
    }
}
?>