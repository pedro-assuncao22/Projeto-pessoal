<?php
session_start();
include("includes/db.php");
include("auth.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar todas comunidades
$sql = "SELECT * FROM communities ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php include("includes/header.php"); ?>

<h2 class="mb-4">💬 Central de Chats</h2>

<!-- Chat geral -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <h5 class="card-title">Chat Geral</h5>
    <p class="card-text">Converse com todos os membros do site em um chat aberto.</p>
    <a href="chat.php" class="btn btn-primary">Entrar no Chat Geral</a>
  </div>
</div>

<!-- Chats por comunidade -->
<h4 class="mb-3">Chats por Comunidade</h4>
<?php while($row = $result->fetch_assoc()): ?>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
      <p class="card-text"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
      <a href="community_chat.php?id=<?= $row['id'] ?>" class="btn btn-success">Entrar no Chat</a>
    </div>
  </div>
<?php endwhile; ?>

<?php include("includes/footer.php"); ?>
