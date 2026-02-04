<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $professional_id = intval($_POST['professional_id'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($professional_id > 0 && $comentario !== '') {
        $sql = "INSERT INTO feedbacks (professional_id, user_id, comentario) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $professional_id, $_SESSION['user_id'], $comentario);
        $stmt->execute();
    }
}

header("Location: professionals.php");
exit;
