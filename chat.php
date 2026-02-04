<?php
session_start();
include("includes/db.php");
include("auth.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Enviar mensagem
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $uid = $_SESSION['user_id'];

    $sql = "INSERT INTO chat_messages (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
}

// Buscar últimas mensagens
$sql = "SELECT cm.*, u.name 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.community_id IS NULL 
        ORDER BY cm.created_at DESC 
        LIMIT 30";
$result = $conn->query($sql);
?>

<?php include("includes/header.php"); ?>

<h2 class="mb-3">💬 Chat Geral</h2>

<div id="chatBox" class="border rounded p-3 mb-3 bg-white" style="height:400px; overflow-y:scroll;">
  <?php while($row = $result->fetch_assoc()): ?>
    <p><b><?= htmlspecialchars($row['name']) ?>:</b> <?= nl2br(htmlspecialchars($row['message'])) ?> 
    <small class="text-muted">(<?= $row['created_at'] ?>)</small></p>
  <?php endwhile; ?>
</div>

<form method="post" action="">
  <div class="input-group">
    <input type="text" name="message" class="form-control" placeholder="Digite sua mensagem..." required>
    <button class="btn btn-primary">Enviar</button>
  </div>
</form>

<?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
  <a href="delete_chat.php" class="btn btn-danger btn-sm mb-3"
     onclick="return confirm('Tem certeza que deseja apagar TODO o chat geral?')">
     Apagar Chat Geral
  </a>
<?php endif; ?>


<script>
// Auto refresh a cada 5 segundos
setInterval(() => {
  fetch("load_chat.php")
    .then(res => res.text())
    .then(html => document.getElementById("chatBox").innerHTML = html);
}, 10);
</script>

<?php include("includes/footer.php"); ?>
