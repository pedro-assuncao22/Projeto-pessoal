<?php
session_start();
include("includes/db.php");
include("auth.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM communities WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: communities.php");
        exit;
    } else {
        echo "Erro ao excluir comunidade.";
    }
} else {
    echo "Comunidade inválida.";
}
