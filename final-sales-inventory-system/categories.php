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
        $query = "SELECT * FROM categories ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $categories = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = array(
                'record_type' => 'category',
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'created_at' => $row['created_at']
            );
        }
        echo json_encode($categories);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO categories (category_id, category_name) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->category_id, $data->category_name])) {
            echo json_encode(array("message" => "Category created."));
        } else {
            echo json_encode(array("message" => "Unable to create category."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->category_id])) {
            echo json_encode(array("message" => "Category deleted."));
        } else {
            echo json_encode(array("message" => "Unable to delete category."));
        }
        break;
}
?>