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
        $query = "SELECT * FROM customers ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $customers = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $customers[] = array(
                'record_type' => 'customer',
                'customer_id' => $row['customer_id'],
                'customer_name' => $row['customer_name'],
                'customer_email' => $row['customer_email'],
                'customer_phone' => $row['customer_phone'],
                'customer_address' => $row['customer_address'],
                'created_at' => $row['created_at']
            );
        }
        echo json_encode($customers);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO customers (customer_id, customer_name, customer_email, customer_phone, customer_address) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([
            $data->customer_id,
            $data->customer_name,
            $data->customer_email,
            $data->customer_phone,
            $data->customer_address
        ])) {
            echo json_encode(array("message" => "Customer created."));
        } else {
            echo json_encode(array("message" => "Unable to create customer."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM customers WHERE customer_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->customer_id])) {
            echo json_encode(array("message" => "Customer deleted."));
        } else {
            echo json_encode(array("message" => "Unable to delete customer."));
        }
        break;
}
?>