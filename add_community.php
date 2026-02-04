<?php
session_start();
include("includes/db.php");

// Verifica se é admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: communities.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['title']);
    $descricao = trim($_POST['description']);
    $link = trim($_POST['link']);
    $created_by = $_SESSION['user_id'];

    if (!empty($titulo) && !empty($descricao)) {
        $sql = "INSERT INTO communities (title, description, link, created_by) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $titulo, $descricao, $link, $created_by);

        if ($stmt->execute()) {
            $sucesso = "Comunidade criada com sucesso!";
        } else {
            $erro = "Erro ao criar comunidade.";
        }
    } else {
        $erro = "Preencha título e descrição.";
    }
}
?>

<?php include("includes/header.php"); ?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card shadow-sm p-4">
      <h3 class="text-center text-success mb-3">Criar Comunidade</h3>

      <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
      <?php endif; ?>

      <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
        <a href="communities.php" class="btn btn-outline-success">Voltar</a>
      <?php else: ?>
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="description" rows="4" class="form-control" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Link (WhatsApp, Telegram, Discord...)</label>
            <input type="url" name="link" class="form-control" placeholder="https://chat.whatsapp.com/..." >
          </div>

          <button type="submit" class="btn btn-success w-100">Criar</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
