<?php
session_start();
header("Content-Type: application/json");
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$clientName = trim($data["client_name"] ?? "");
$address = trim($data["address"] ?? "");
$phone = trim($data["phone"] ?? "");
$registration = strtoupper(trim($data["car_registration"] ?? ""));
$engine = trim($data["engine_number"] ?? "");
$date = $data["appointment_date"] ?? "";
$mechanicId = filter_var($data["mechanic_id"] ?? "", FILTER_VALIDATE_INT);

if ($clientName === "" || $address === "" || $phone === "" ||
    $registration === "" || $engine === "" || !$mechanicId || $date === "") {
    http_response_code(400);
    echo json_encode(["message" => "Please complete every required field."]);
    exit;
}

if (!preg_match("/^[0-9+() -]{7,25}$/", $phone)) {
    http_response_code(400);
    echo json_encode(["message" => "Please enter a valid phone number."]);
    exit;
}

if (!preg_match("/^[0-9A-Za-z-]+$/", $engine)) {
    http_response_code(400);
    echo json_encode(["message" => "Engine number can contain letters, numbers and hyphens only."]);
    exit;
}

$dateObject = DateTime::createFromFormat("Y-m-d", $date);
if (!$dateObject || $dateObject->format("Y-m-d") !== $date || $date < date("Y-m-d")) {
    http_response_code(400);
    echo json_encode(["message" => "Please choose today or a future date."]);
    exit;
}

try {
    $pdo->beginTransaction();

    $lock = $pdo->prepare("SELECT id FROM mechanics WHERE id = ? FOR UPDATE");
    $lock->execute([$mechanicId]);
    if (!$lock->fetch()) {
        throw new Exception("The selected mechanic was not found.");
    }

    $duplicate = $pdo->prepare(
        "SELECT id FROM appointments
         WHERE car_registration = ? AND appointment_date = ?"
    );

    $duplicate->execute([$registration, $date]);
    
    if ($duplicate->fetch()) {
        throw new Exception("This car already has an appointment on that date.");
    }

    $engineDuplicate = $pdo->prepare(
        "SELECT id FROM appointments
        WHERE engine_number = ? AND appointment_date = ?"
    );

    $engineDuplicate->execute([$engine, $date]);

    if ($engineDuplicate->fetch()) {
        throw new Exception("This engine number already has an appointment on that date.");
    }

    $count = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments WHERE mechanic_id = ? AND appointment_date = ?"
    );
    $count->execute([$mechanicId, $date]);
    if ((int)$count->fetchColumn() >= 4) {
        throw new Exception("This mechanic is fully booked on that date. Please choose another mechanic or date.");
    }

    $insert = $pdo->prepare(
        "INSERT INTO appointments
        (client_name, address, phone, car_registration, engine_number, appointment_date, mechanic_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $insert->execute([$clientName, $address, $phone, $registration, $engine, $date, $mechanicId]);

    $pdo->commit();
    echo json_encode(["message" => "Your appointment has been confirmed. Thank you!"]);
} catch (Exception $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(["message" => $error->getMessage()]);
}
?>