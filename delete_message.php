<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Deletar a mensagem específica
    $sql = "DELETE FROM chat_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redireciona de volta para a página anterior
        if (isset($_GET['community_id'])) {
            header("Location: community_chat.php?id=" . intval($_GET['community_id']));
        } else {
            header("Location: chat.php");
        }
        exit;
    } else {
        echo "Erro ao excluir mensagem.";
    }
} else {
    echo "Mensagem inválida.";
}
