<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $query = "SELECT s.*, p.product_name, c.customer_name, e.employee_name 
                 FROM sales s 
                 LEFT JOIN products p ON s.sale_product_id = p.product_id 
                 LEFT JOIN customers c ON s.sale_customer_id = c.customer_id 
                 LEFT JOIN employees e ON s.sale_employee_id = e.employee_id 
                 ORDER BY s.sale_date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $sales = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sales[] = array(
                'record_type' => 'sale',
                'sale_id' => $row['sale_id'],
                'sale_date' => $row['sale_date'],
                'sale_product_id' => $row['sale_product_id'],
                'sale_product_name' => $row['product_name'],
                'sale_quantity' => intval($row['sale_quantity']),
                'sale_unit_price' => floatval($row['sale_unit_price']),
                'sale_total_amount' => floatval($row['sale_total_amount']),
                'sale_customer_id' => $row['sale_customer_id'],
                'sale_customer_name' => $row['customer_name'],
                'sale_employee_id' => $row['sale_employee_id'],
                'sale_employee_name' => $row['employee_name'],
                'created_at' => $row['created_at']
            );
        }
        echo json_encode($sales);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Insert sale
            $saleQuery = "INSERT INTO sales (sale_id, sale_product_id, sale_quantity, sale_unit_price, sale_total_amount, sale_customer_id, sale_employee_id) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            $saleStmt = $db->prepare($saleQuery);
            $saleStmt->execute([
                $data->sale_id,
                $data->sale_product_id,
                $data->sale_quantity,
                $data->sale_unit_price,
                $data->sale_total_amount,
                $data->sale_customer_id,
                $data->sale_employee_id
            ]);
            
            // Update product inventory
            $updateQuery = "UPDATE products SET quantity_in_stock = quantity_in_stock - ?, updated_at = CURRENT_TIMESTAMP WHERE product_id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$data->sale_quantity, $data->sale_product_id]);
            
            $db->commit();
            echo json_encode(array("message" => "Sale recorded and inventory updated."));
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(array("message" => "Failed to record sale: " . $e->getMessage()));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Get sale details first to restore inventory
            $getQuery = "SELECT sale_product_id, sale_quantity FROM sales WHERE sale_id = ?";
            $getStmt = $db->prepare($getQuery);
            $getStmt->execute([$data->sale_id]);
            $sale = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sale) {
                // Restore inventory
                $restoreQuery = "UPDATE products SET quantity_in_stock = quantity_in_stock + ?, updated_at = CURRENT_TIMESTAMP WHERE product_id = ?";
                $restoreStmt = $db->prepare($restoreQuery);
                $restoreStmt->execute([$sale['sale_quantity'], $sale['sale_product_id']]);
                
                // Delete sale
                $deleteQuery = "DELETE FROM sales WHERE sale_id = ?";
                $deleteStmt = $db->prepare($deleteQuery);
                $deleteStmt->execute([$data->sale_id]);
                
                $db->commit();
                echo json_encode(array("message" => "Sale deleted and inventory restored."));
            } else {
                $db->rollBack();
                echo json_encode(array("message" => "Sale not found."));
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(array("message" => "Failed to delete sale: " . $e->getMessage()));
        }
        break;
}
?>