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

$request_json = file_get_contents("php://input");
$request_body = json_decode($request_json); 
$request_headers = getallheaders();

// Om metoden inte är GET kollar vilken user_id API key har
if ($_SERVER["REQUEST_METHOD"] != "GET" && isset($request_headers["x-api-key"])) {
  $key = test_input($request_headers["x-api-key"]);
  $query = "SELECT id FROM startpage_users WHERE api_key = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $global_user_id = $response[0];
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $key = test_input($request_vars["key"]);
  
  $query = "SELECT t.id, t.title, t.complete, t.due_date, t.created_at, t.updated_at,
            (SELECT c.name AS category FROM startpage_categories c WHERE c.id = t.category_id),
            (SELECT c.color AS color FROM startpage_categories c WHERE c.id = t.category_id)
            FROM startpage_users u 
            INNER JOIN startpage_tasks t ON u.id = t.user_id
            WHERE u.api_key = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    $query = "INSERT INTO startpage_tasks 
                   (user_id, category_id, title, complete) 
            VALUES (:user_id, :category_id, :title, :complete)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      "user_id" => $request_body->user_id,
      "category_id" => $request_body->category_id,
      "title" => test_input($request_body->title),
      "complete" => $request_body->complete
    ]);

    $response = [
    "msg" => "saved",
    "body" => $request_body,
    "headers" => $request_headers,
    "uid" => $global_user_id
    ];
  } catch (Exception $e) {
    $response = ["error" => $e, "body" => $request_body];
  }
}

if (empty($response)) {
  $response = [
    "error" => "invalid api key or the query returned no results"
  ];
}

echo json_encode($response);