<?php
session_start();
require_once "includes/db.php";

// Verifica se é admin OU profissional
if (
    !isset($_SESSION['user_role']) || 
    !in_array($_SESSION['user_role'], ['admin', 'professional'])
) {
    die("Acesso negado. Apenas administradores ou profissionais podem acessar esta página.");
}

$erro = "";
$sucesso = "";

// ... resto do código igual (formulário e inserção) ...


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $rua = trim($_POST['rua']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $telefone = trim($_POST['telefone']);
    $contato_extra = trim($_POST['contato_extra']);

    if ($nome === "" || $rua === "" || $bairro === "" || $cidade === "") {
        $erro = "Preencha todos os campos obrigatórios (Nome, Rua, Bairro, Cidade).";
    } else {
        $sql = "INSERT INTO clinics (nome, rua, bairro, cidade, telefone, contato_extra) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssss", $nome, $rua, $bairro, $cidade, $telefone, $contato_extra);
            if ($stmt->execute()) {
                $sucesso = "Clínica cadastrada com sucesso!";
            } else {
                $erro = "Erro ao cadastrar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $erro = "Erro interno: " . $conn->error;
        }
    }
}

include("includes/header.php");
?>

<div class="container my-4">
  <h2 class="mb-3">Cadastrar Nova Clínica</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <a href="clinics.php" class="btn btn-primary">Ver Clínicas</a>
  <?php else: ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Nome da Clínica</label>
        <input type="text" name="nome" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Rua</label>
        <input type="text" name="rua" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Bairro</label>
        <input type="text" name="bairro" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Cidade</label>
        <input type="text" name="cidade" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Telefone</label>
        <input type="text" name="telefone" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Outro contato (opcional)</label>
        <input type="text" name="contato_extra" class="form-control">
      </div>
      <button type="submit" class="btn btn-success">Cadastrar Clínica</button>
    </form>
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
