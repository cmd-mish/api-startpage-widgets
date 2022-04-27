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

$request_authorised = false;

// Om metoden inte är GET kollar vilken user_id API key har och sparar användarens id i $global_user_id
if ($_SERVER["REQUEST_METHOD"] != "GET" && !empty($request_headers["x-api-key"]) && isset($request_headers["x-api-key"])) {
  $key = test_input($request_headers["x-api-key"]);
  $query = "SELECT id FROM startpage_users WHERE api_key = :api_key";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetch(PDO::FETCH_ASSOC);
  $global_user_id = $response["id"];
  $request_authorised = true;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && test_input($request_vars["content"] == "tasks")) {
  $key = test_input($request_vars["key"]);
  
  $query = "SELECT t.id, t.title, t.complete, t.due_date, t.created_at, t.updated_at,
            (SELECT c.name AS category FROM startpage_categories c WHERE c.id = t.category_id),
            (SELECT c.color AS color FROM startpage_categories c WHERE c.id = t.category_id)
            FROM startpage_users u 
            INNER JOIN startpage_tasks t ON u.id = t.user_id
            WHERE u.api_key = :api_key
            ORDER BY t.id";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else if (($_SERVER["REQUEST_METHOD"] == "GET" && test_input($request_vars["content"] == "categories"))){
  $query = "SELECT name, id from startpage_categories ORDER BY id";
  $stmt = $pdo->prepare($query);
  $stmt->execute();

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else if ($_SERVER["REQUEST_METHOD"] == "POST" && $request_authorised) {
  try {
    $query = "INSERT INTO startpage_tasks 
                   (user_id, category_id, title, complete) 
            VALUES (:user_id, :category_id, :title, :complete)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      "user_id" => $global_user_id,
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
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE" && $request_authorised) {
  try {
    $query = "DELETE FROM startpage_tasks WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["id" => test_input($request_vars["id"]), "user_id" => $global_user_id]);
    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($response)) {
      $response = [ "status" => "Deleted task " . test_input($request_vars['id'])];
    }
  } catch (Exception $e) {
    $response = ["error" => $e, "body" => $request_body];
  }
} else if ($_SERVER["REQUEST_METHOD"] == "PUT" && $request_authorised) {
  try {
    $query = "UPDATE startpage_tasks SET complete = :complete, updated_at = :updated_at WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["complete" => test_input($request_vars["complete"]), "updated_at" => "now()", "id" => test_input($request_vars["id"]), "user_id" => $global_user_id]);
    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($response)) {
      $response = [ "status" => "Updated task " . test_input($request_vars['id'])];
    }
  } catch (Exception $e) {
    $response = ["error" => $e, "body" => $request_body];
  }
}

if (empty($response)) {
  $response = [
    "error" => "unsuccessful request"
  ];
}

echo json_encode($response);