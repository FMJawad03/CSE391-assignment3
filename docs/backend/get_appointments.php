<?php
require "auth.php";
require "db.php";

$sql = "SELECT a.id, a.client_name, a.phone, a.car_registration,
               a.engine_number, a.appointment_date, a.mechanic_id, m.name AS mechanic_name
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        ORDER BY a.appointment_date DESC, a.id DESC";
$stmt = $pdo->query($sql);
echo json_encode(["appointments" => $stmt->fetchAll()]);
?>