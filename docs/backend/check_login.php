<?php
require "auth.php";
echo json_encode(["logged_in" => true, "username" => $_SESSION["admin_username"]]);
?>
