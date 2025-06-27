<?php
$servername = "sql103.infinityfree.com";
$username = "if0_39100338";
$password = "BgJarLriBMr5X0K";
$database = "if0_39100338_lostandfoundportal";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
