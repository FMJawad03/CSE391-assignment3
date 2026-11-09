<?php
header("Content-Type: application/json");
require "db.php";

$date = $_GET["date"] ?? "";

$dateObject = DateTime::createFromFormat("Y-m-d", $date);
if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
    http_response_code(400);
    echo json_encode(["message" => "Please choose a valid date."]);
    exit;
}

$sql = "SELECT m.id, m.name,
        (4 - COUNT(a.id)) AS free_slots
        FROM mechanics m
        LEFT JOIN appointments a
            ON a.mechanic_id = m.id AND a.appointment_date = :appointment_date
        GROUP BY m.id, m.name
        ORDER BY m.id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["appointment_date" => $date]);

echo json_encode(["mechanics" => $stmt->fetchAll()]);
?>
