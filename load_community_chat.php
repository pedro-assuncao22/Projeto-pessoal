<?php
session_start();
include("includes/db.php");

$community_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

$sql = "SELECT cm.*, u.name 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.community_id = ? 
        ORDER BY cm.created_at ASC 
        LIMIT 50";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $community_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    echo "<p><b>".htmlspecialchars($row['name']).":</b> ".
         nl2br(htmlspecialchars($row['message'])).
         " <small class='text-muted'>(".$row['created_at'].")</small>";

    if ($isAdmin) {
        echo " <a href='delete_message.php?id=".$row['id']."&community_id=".$community_id."' 
                class='text-danger small ms-2'
                onclick='return confirm(\"Excluir esta mensagem?\")'>[Excluir]</a>";
    }

    echo "</p>";
}
