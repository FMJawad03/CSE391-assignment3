<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Please log in as an admin."]);
    exit;
}
?>
