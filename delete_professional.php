<?php
session_start();
require_once "includes/db.php";
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado.");
}
if (!isset($_GET['id'])) { header("Location: manage_professionals.php"); exit; }
$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM professionals WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
header("Location: manage_professionals.php");
exit;
