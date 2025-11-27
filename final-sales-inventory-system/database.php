<?php
class Database {
    private $db_file = "inventory.db";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // SQLite connection - no username/password needed
            $this->conn = new PDO("sqlite:" . $this->db_file);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign keys
            $this->conn->exec("PRAGMA foreign_keys = ON");
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Create tables automatically
function createTables($db) {
    try {
        // Categories table
        $db->exec("CREATE TABLE IF NOT EXISTS categories (
            category_id VARCHAR(50) PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Suppliers table
        $db->exec("CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id VARCHAR(50) PRIMARY KEY,
            supplier_name VARCHAR(100) NOT NULL,
            supplier_contact VARCHAR(100),
            supplier_email VARCHAR(100),
            supplier_phone VARCHAR(20),
            supplier_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Products table
        $db->exec("CREATE TABLE IF NOT EXISTS products (
            product_id VARCHAR(50) PRIMARY KEY,
            product_name VARCHAR(100) NOT NULL,
            product_description TEXT,
            category_id VARCHAR(50),
            unit_price DECIMAL(10,2) NOT NULL,
            quantity_in_stock INTEGER NOT NULL,
            reorder_level INTEGER NOT NULL,
            supplier_id VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(category_id),
            FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
        )");

        // Customers table
        $db->exec("CREATE TABLE IF NOT EXISTS customers (
            customer_id VARCHAR(50) PRIMARY KEY,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100),
            customer_phone VARCHAR(20),
            customer_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Employees table
        $db->exec("CREATE TABLE IF NOT EXISTS employees (
            employee_id VARCHAR(50) PRIMARY KEY,
            employee_name VARCHAR(100) NOT NULL,
            employee_position VARCHAR(100),
            employee_email VARCHAR(100),
            employee_phone VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Sales table
        $db->exec("CREATE TABLE IF NOT EXISTS sales (
            sale_id VARCHAR(50) PRIMARY KEY,
            sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            sale_product_id VARCHAR(50),
            sale_quantity INTEGER NOT NULL,
            sale_unit_price DECIMAL(10,2) NOT NULL,
            sale_total_amount DECIMAL(10,2) NOT NULL,
            sale_customer_id VARCHAR(50),
            sale_employee_id VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_product_id) REFERENCES products(product_id),
            FOREIGN KEY (sale_customer_id) REFERENCES customers(customer_id),
            FOREIGN KEY (sale_employee_id) REFERENCES employees(employee_id)
        )");

        // Insert sample data
        insertSampleData($db);
        
    } catch (Exception $e) {
        echo "Error creating tables: " . $e->getMessage();
    }
}

// Insert sample data
function insertSampleData($db) {
    try {
        // Sample categories
        $categories = [
            ['cat_1', 'Electronics'],
            ['cat_2', 'Office Supplies'],
            ['cat_3', 'Furniture']
        ];
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO categories (category_id, category_name) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        
        // Sample suppliers
        $suppliers = [
            ['sup_1', 'Tech Supplies Inc.', 'John Smith', 'john@techsupplies.com', '555-123-4567', '123 Tech Street'],
            ['sup_2', 'Office World', 'Sarah Johnson', 'sarah@officeworld.com', '555-987-6543', '456 Office Ave']
        ];
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO suppliers (supplier_id, supplier_name, supplier_contact, supplier_email, supplier_phone, supplier_address) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($suppliers as $supplier) {
            $stmt->execute($supplier);
        }
        
        // Sample products
        $products = [
            ['prod_1', 'Wireless Mouse', 'High-quality wireless mouse', 'cat_1', 29.99, 15, 5, 'sup_1'],
            ['prod_2', 'Mechanical Keyboard', 'RGB mechanical keyboard', 'cat_1', 89.99, 8, 3, 'sup_1'],
            ['prod_3', 'Office Notebook', 'A4 size notebook', 'cat_2', 4.99, 2, 10, 'sup_2']
        ];
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO products (product_id, product_name, product_description, category_id, unit_price, quantity_in_stock, reorder_level, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        
    } catch (Exception $e) {
        // Ignore errors - data might already exist
    }
}

// Initialize database and tables
$database = new Database();
$db = $database->getConnection();
createTables($db);
?>