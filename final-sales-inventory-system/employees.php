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
        $query = "SELECT * FROM employees ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $employees = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $employees[] = array(
                'record_type' => 'employee',
                'employee_id' => $row['employee_id'],
                'employee_name' => $row['employee_name'],
                'employee_position' => $row['employee_position'],
                'employee_email' => $row['employee_email'],
                'employee_phone' => $row['employee_phone'],
                'created_at' => $row['created_at']
            );
        }
        echo json_encode($employees);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO employees (employee_id, employee_name, employee_position, employee_email, employee_phone) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([
            $data->employee_id,
            $data->employee_name,
            $data->employee_position,
            $data->employee_email,
            $data->employee_phone
        ])) {
            echo json_encode(array("message" => "Employee created."));
        } else {
            echo json_encode(array("message" => "Unable to create employee."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM employees WHERE employee_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->employee_id])) {
            echo json_encode(array("message" => "Employee deleted."));
        } else {
            echo json_encode(array("message" => "Unable to delete employee."));
        }
        break;
}
?>