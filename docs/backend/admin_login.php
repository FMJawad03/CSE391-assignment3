<?php
session_start();
header("Content-Type: application/json");
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

$stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin["password_hash"])) {
    session_regenerate_id(true);
    $_SESSION["admin_id"] = $admin["id"];
    $_SESSION["admin_username"] = $admin["username"];
    echo json_encode(["message" => "Login successful."]);
} else {
    http_response_code(401);
    echo json_encode(["message" => "Incorrect username or password."]);
}
?>
