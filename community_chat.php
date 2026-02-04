<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$community_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Busca dados da comunidade
$sql = "SELECT * FROM communities WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $community_id);
$stmt->execute();
$community = $stmt->get_result()->fetch_assoc();

if (!$community) {
    die("Comunidade não encontrada.");
}

// Enviar mensagem
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $uid = $_SESSION['user_id'];

    $sql = "INSERT INTO chat_messages (user_id, message, community_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $uid, $msg, $community_id);
    $stmt->execute();
}
?>

<?php include("includes/header.php"); ?>

<h2 class="mb-3">💬 Chat da Comunidade: <?= htmlspecialchars($community['title']) ?></h2>

<div id="chatBox" class="border rounded p-3 mb-3 bg-white" style="height:400px; overflow-y:scroll;">
  <!-- Mensagens via load_community_chat.php -->
</div>

<form method="post" action="" class="d-flex">
  <input type="text" name="message" class="form-control me-2" placeholder="Digite sua mensagem..." required>
  <button class="btn btn-success">Enviar</button>
</form>

<?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
  <a href="delete_chat.php?community_id=<?= $community_id ?>" 
     class="btn btn-danger btn-sm mb-3"
     onclick="return confirm('Tem certeza que deseja apagar TODO o chat desta comunidade?')">
     Apagar Chat da Comunidade
  </a>
<?php endif; ?>


<script>
function loadCommunityChat() {
  fetch("load_community_chat.php?id=<?= $community_id ?>")
    .then(res => res.text())
    .then(html => document.getElementById("chatBox").innerHTML = html);
}
setInterval(loadCommunityChat, 100);
loadCommunityChat();
</script>

<?php include("includes/footer.php"); ?>
