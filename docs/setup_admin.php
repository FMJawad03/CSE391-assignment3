<?php
require "backend/db.php";

$username = "admin";
$password = "Fmj123@";

$check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
$check->execute([$username]);

if ($check->fetch()) {
    echo "The admin account already exists. Delete setup_admin.php from your server.";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$insert = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
$insert->execute([$username, $hash]);

echo "Admin account created.<br>";
echo "Username: admin<br>";
echo "Password: Fmj123@<br>";
?>