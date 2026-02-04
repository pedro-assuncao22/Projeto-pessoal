<?php
session_start();
include("includes/db.php");

$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

$sql = "SELECT cm.*, u.name 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.community_id IS NULL 
        ORDER BY cm.created_at ASC 
        LIMIT 50";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    echo "<p><b>".htmlspecialchars($row['name']).":</b> ".
         nl2br(htmlspecialchars($row['message'])).
         " <small class='text-muted'>(".$row['created_at'].")</small>";

    if ($isAdmin) {
        echo " <a href='delete_message.php?id=".$row['id']."' 
                class='text-danger small ms-2'
                onclick='return confirm(\"Excluir esta mensagem?\")'>[Excluir]</a>";
    }

    echo "</p>";
}
