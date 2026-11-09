<?php
require "auth.php";
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = filter_var($data["id"] ?? "", FILTER_VALIDATE_INT);
$date = $data["appointment_date"] ?? "";
$mechanicId = filter_var($data["mechanic_id"] ?? "", FILTER_VALIDATE_INT);

$dateObject = DateTime::createFromFormat("Y-m-d", $date);
if (!$id || !$mechanicId || !$dateObject || $dateObject->format("Y-m-d") !== $date || $date < date("Y-m-d")) {
    http_response_code(400);
    echo json_encode(["message" => "Please enter a valid appointment and date."]);
    exit;
}

try {
    $pdo->beginTransaction();

    $currentStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $currentStmt->execute([$id]);
    $current = $currentStmt->fetch();
    if (!$current) {
        throw new Exception("Appointment not found.");
    }

    $lock = $pdo->prepare("SELECT id FROM mechanics WHERE id = ? FOR UPDATE");
    $lock->execute([$mechanicId]);
    if (!$lock->fetch()) {
        throw new Exception("The selected mechanic was not found.");
    }

    $duplicate = $pdo->prepare(
        "SELECT id FROM appointments
         WHERE car_registration = ? AND appointment_date = ? AND id <> ?"
    );

    $duplicate->execute([$current["car_registration"], $date, $id]);

    if ($duplicate->fetch()) {
        throw new Exception("This car already has another appointment on that date.");
    }

    $engineDuplicate = $pdo->prepare(
        "SELECT id FROM appointments
        WHERE engine_number = ? AND appointment_date = ? AND id <> ?"
    );

    $engineDuplicate->execute([$current["engine_number"], $date, $id]);

    if ($engineDuplicate->fetch()) {
        throw new Exception("This engine number already has another appointment on that date.");
    }

    $count = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments
         WHERE mechanic_id = ? AND appointment_date = ? AND id <> ?"
    );
    $count->execute([$mechanicId, $date, $id]);
    if ((int)$count->fetchColumn() >= 4) {
        throw new Exception("That mechanic already has 4 appointments on the selected date.");
    }

    $update = $pdo->prepare(
        "UPDATE appointments SET appointment_date = ?, mechanic_id = ? WHERE id = ?"
    );
    $update->execute([$date, $mechanicId, $id]);

    $pdo->commit();
    echo json_encode(["message" => "Appointment updated successfully."]);
} catch (Exception $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(["message" => $error->getMessage()]);
}
?>