<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $query = "SELECT p.*, c.category_name, s.supplier_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.category_id 
                 LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                 ORDER BY p.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $products = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = array(
                'record_type' => 'product',
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'product_description' => $row['product_description'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'unit_price' => floatval($row['unit_price']),
                'quantity_in_stock' => intval($row['quantity_in_stock']),
                'reorder_level' => intval($row['reorder_level']),
                'supplier_id' => $row['supplier_id'],
                'supplier_name' => $row['supplier_name'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            );
        }
        echo json_encode($products);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO products (product_id, product_name, product_description, category_id, unit_price, quantity_in_stock, reorder_level, supplier_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([
            $data->product_id,
            $data->product_name,
            $data->product_description,
            $data->category_id,
            $data->unit_price,
            $data->quantity_in_stock,
            $data->reorder_level,
            $data->supplier_id
        ])) {
            echo json_encode(array("message" => "Product created."));
        } else {
            echo json_encode(array("message" => "Unable to create product."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "UPDATE products 
                 SET product_name = ?, product_description = ?, category_id = ?, unit_price = ?, 
                     quantity_in_stock = ?, reorder_level = ?, supplier_id = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE product_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([
            $data->product_name,
            $data->product_description,
            $data->category_id,
            $data->unit_price,
            $data->quantity_in_stock,
            $data->reorder_level,
            $data->supplier_id,
            $data->product_id
        ])) {
            echo json_encode(array("message" => "Product updated."));
        } else {
            echo json_encode(array("message" => "Unable to update product."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM products WHERE product_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->product_id])) {
            echo json_encode(array("message" => "Product deleted."));
        } else {
            echo json_encode(array("message" => "Unable to delete product."));
        }
        break;
}
?>