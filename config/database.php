<?php
/**
 * Database Configuration and Connection
 * Handles MySQL connection and provides helper functions
 */

// Database credentials — read from environment (Docker) or fall back to local dev values
$db_host     = getenv('DB_HOST')     ?: '127.0.0.1';
$db_port     = (int)(getenv('DB_PORT')     ?: 3306);
$db_user     = getenv('DB_USER')     ?: 'vscode';
$db_password = getenv('DB_PASSWORD') ?: '23264008';
$db_name     = getenv('DB_NAME')     ?: 'school';

// Create connection using mysqli (improved MySQL extension)
$conn = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 (for emoji and special characters)
$conn->set_charset("utf8mb4");

/**
 * Execute a query
 * @param string $sql - The SQL query
 * @param string $types - Parameter types (e.g., 'ss' for two strings)
 * @param array $params - Parameters to bind (for prepared statements)
 * @return mixed - Result object or false on error
 */
function executeQuery($sql, $types = '', $params = []) {
    global $conn;
    
    if (!empty($params)) {
        // Prepared statement for security (prevents SQL injection)
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt;
    } else {
        // Simple query without parameters
        return $conn->query($sql);
    }
}

/**
 * Fetch a single row as associative array
 * @param string $sql - The SQL query
 * @param string $types - Parameter types
 * @param array $params - Parameters to bind
 * @return array|null - Row as array or null if not found
 */
function fetchOne($sql, $types = '', $params = []) {
    $result = executeQuery($sql, $types, $params);
    
    if (!empty($params)) {
        // Result from prepared statement
        $result = $result->get_result();
    }
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Fetch all rows as associative array
 * @param string $sql - The SQL query
 * @param string $types - Parameter types
 * @param array $params - Parameters to bind
 * @return array - Array of rows
 */
function fetchAll($sql, $types = '', $params = []) {
    $result = executeQuery($sql, $types, $params);
    
    if (!empty($params)) {
        // Result from prepared statement
        $result = $result->get_result();
    }
    
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Insert data into database
 * @param string $sql - The INSERT query
 * @param string $types - Parameter types
 * @param array $params - Parameters to bind
 * @return int|false - Last inserted ID or false on error
 */
function insertData($sql, $types = '', $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $conn->insert_id;
}

/**
 * Update/Delete data from database
 * @param string $sql - The UPDATE/DELETE query
 * @param string $types - Parameter types
 * @param array $params - Parameters to bind
 * @return int|false - Affected rows or false on error
 */
function updateData($sql, $types = '', $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $conn->affected_rows;
}

/**
 * Hash a password using bcrypt
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 * @param string $password - Plain text password
 * @param string $hash - Password hash from database
 * @return bool - True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user
 * @return array|null - User data or null
 */
function getCurrentUser() {
    startSession();
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT user_id, username, email FROM users WHERE user_id = ?";
    return fetchOne($sql, 'i', [$_SESSION['user_id']]);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Start session automatically
startSession();
?>