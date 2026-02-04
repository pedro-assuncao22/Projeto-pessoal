<?php
session_start();
require_once "includes/db.php";
include("includes/header.php");

// Apenas admins ou profissionais verificados podem acessar
$canAdd = false;

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        $canAdd = true;
    } elseif ($_SESSION['user_role'] === 'professional') {
        $userId = (int)$_SESSION['user_id'];
        $sqlProf = "SELECT verificado FROM professionals WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sqlProf);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resProf = $stmt->get_result();
        if ($resProf && $prof = $resProf->fetch_assoc()) {
            if ($prof['verificado'] == 1) {
                $canAdd = true;
            }
        }
        $stmt->close();
    }
}

if (!$canAdd) {
    die("<div class='container my-4'><div class='alert alert-danger'>Acesso negado. Apenas administradores ou profissionais verificados podem adicionar reflexões.</div></div>");
}

$erro = "";
$sucesso = "";

// Processa envio
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === "" || $content === "") {
        $erro = "Preencha todos os campos.";
    } else {
        $sql = "INSERT INTO reflections (title, content, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            $sucesso = "Reflexão adicionada com sucesso!";
        } else {
            $erro = "Erro ao salvar: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="container my-4">
  <h2 class="mb-3">Adicionar Reflexão</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <a href="reflections.php" class="btn btn-primary mt-2">Voltar às Reflexões</a>
  <?php else: ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Título</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Conteúdo</label>
        <textarea name="content" rows="5" class="form-control" required></textarea>
      </div>
      <button type="submit" class="btn btn-success">Salvar Reflexão</button>
    </form>
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
