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
        $query = "SELECT * FROM suppliers ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $suppliers = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $suppliers[] = array(
                'record_type' => 'supplier',
                'supplier_id' => $row['supplier_id'],
                'supplier_name' => $row['supplier_name'],
                'supplier_contact' => $row['supplier_contact'],
                'supplier_email' => $row['supplier_email'],
                'supplier_phone' => $row['supplier_phone'],
                'supplier_address' => $row['supplier_address'],
                'created_at' => $row['created_at']
            );
        }
        echo json_encode($suppliers);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO suppliers (supplier_id, supplier_name, supplier_contact, supplier_email, supplier_phone, supplier_address) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([
            $data->supplier_id,
            $data->supplier_name,
            $data->supplier_contact,
            $data->supplier_email,
            $data->supplier_phone,
            $data->supplier_address
        ])) {
            echo json_encode(array("message" => "Supplier created."));
        } else {
            echo json_encode(array("message" => "Unable to create supplier."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM suppliers WHERE supplier_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->supplier_id])) {
            echo json_encode(array("message" => "Supplier deleted."));
        } else {
            echo json_encode(array("message" => "Unable to delete supplier."));
        }
        break;
}
?>