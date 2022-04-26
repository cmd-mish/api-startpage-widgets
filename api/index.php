<?php
require_once("../../../../local/frontpage_config.php");

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
  
  $query = "SELECT username, widgets FROM startpage_users WHERE api_key = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$key]);

  $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($response)) {
  $response = [
    "error" => "invalid api key or the query returned no results"
  ];
}

echo json_encode($response);