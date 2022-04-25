<?php

require_once("../../../../../local/frontpage_config.php");

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// PDO
try {
  $conn_string = "pgsql:host=" 
      . $db_conf['host'] 
      . ";port=5432;dbname="
      . $db_conf["name"];
  $pdo = new PDO($conn_string, 
      $db_conf["user"], 
      $db_conf["password"],
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
  die($e->getMessage());
}

header("Content-Type: application/json");
parse_str($_SERVER['QUERY_STRING'], $request_vars);
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $key = test_input($request_vars["key"]);
  
  $query = "SELECT t.title, t.complete, t.due_date, t.created_at, t.updated_at,
            (SELECT c.name AS category FROM startpage_categories c WHERE c.id = t.category_id),
            (SELECT c.color AS color FROM startpage_categories c WHERE c.id = t.category_id)
            FROM startpage_users u 
            INNER JOIN startpage_tasks t ON u.id = t.user_id
            WHERE u.api_key = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($response)) {
  $response = [
    "error" => "invalid api key"
  ];
}

echo json_encode($response);