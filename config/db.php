<?php
/**
 * Database Connection for UMUHUZA Cooperative Management System
 * Uses MySQLi with PDO for database operations
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cooperative_db');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            // Create MySQLi connection
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get connection
    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // Create database and tables
    public static function setup() {
        try {
            // Connect without database first
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Create database if not exists
            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
            if (!$conn->query($sql)) {
                throw new Exception("Error creating database: " . $conn->error);
            }

            // Select database
            $conn->select_db(DB_NAME);
            $conn->set_charset("utf8mb4");

            // Create Members table
            $sql = "CREATE TABLE IF NOT EXISTS members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                village VARCHAR(100) NOT NULL,
                join_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($sql);

            // Create Products table
            $sql = "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'Maize',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            )";
            $conn->query($sql);

            // Create Clients table
            $sql = "CREATE TABLE IF NOT EXISTS clients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                location VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($sql);

            // Create Sales table
            $sql = "CREATE TABLE IF NOT EXISTS sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                sale_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )";
            $conn->query($sql);

            // Create Admins table
            $sql = "CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($sql);

            $conn->close();
            return true;
        } catch (Exception $e) {
            die("Database setup error: " . $e->getMessage());
        }
    }
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to get database instance
function getDB() {
    return Database::getInstance();
}
