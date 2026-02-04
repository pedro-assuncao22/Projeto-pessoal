<?php
session_start();
require_once "includes/db.php";

// Apenas admin OU profissional verificado
$canDelete = false;

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        $canDelete = true;
    } elseif ($_SESSION['user_role'] === 'professional') {
        $userId = (int)$_SESSION['user_id'];
        $sql = "SELECT verificado FROM professionals WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $prof = $res->fetch_assoc()) {
            if ($prof['verificado'] == 1) {
                $canDelete = true;
            }
        }
        $stmt->close();
    }
}

if (!$canDelete) {
    die("Acesso negado.");
}

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
    die("Reflexão inválida.");
}

$id = intval($_GET['id']);

// Exclui a reflexão
$sql = "DELETE FROM reflections WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: reflections.php");
    exit;
} else {
    echo "Erro ao excluir reflexão.";
}
