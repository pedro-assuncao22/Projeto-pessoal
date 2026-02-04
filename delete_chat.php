<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;

if ($community_id) {
    $sql = "DELETE FROM chat_messages WHERE community_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
} else {
    // Chat geral
    $conn->query("DELETE FROM chat_messages WHERE community_id IS NULL");
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
