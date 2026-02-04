<?php
session_start();
require_once "includes/db.php";

// Apenas admins podem acessar
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem acessar esta página.");
}

$erro = "";
$sucesso = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Busca clínica existente
$sql = "SELECT * FROM clinics WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$clinica = $res->fetch_assoc();
$stmt->close();

if (!$clinica) {
    die("Clínica não encontrada.");
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $rua = trim($_POST['rua']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $telefone = trim($_POST['telefone']);
    $contato_extra = trim($_POST['contato_extra']);

    if ($nome === "" || $rua === "" || $bairro === "" || $cidade === "") {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        $sql = "UPDATE clinics 
                SET nome = ?, rua = ?, bairro = ?, cidade = ?, telefone = ?, contato_extra = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $nome, $rua, $bairro, $cidade, $telefone, $contato_extra, $id);

        if ($stmt->execute()) {
            $sucesso = "Clínica atualizada com sucesso!";
            // Atualiza os dados exibidos
            $clinica['nome'] = $nome;
            $clinica['rua'] = $rua;
            $clinica['bairro'] = $bairro;
            $clinica['cidade'] = $cidade;
            $clinica['telefone'] = $telefone;
            $clinica['contato_extra'] = $contato_extra;
        } else {
            $erro = "Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    }
}

include("includes/header.php");
?>

<div class="container my-4">
  <h2 class="mb-3">Editar Clínica</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>
  <?php if ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <a href="manage_clinics.php" class="btn btn-primary">Voltar à lista</a>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nome da Clínica</label>
      <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($clinica['nome']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Rua</label>
      <input type="text" name="rua" class="form-control" value="<?= htmlspecialchars($clinica['rua']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Bairro</label>
      <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($clinica['bairro']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Cidade</label>
      <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($clinica['cidade']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Telefone</label>
      <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($clinica['telefone']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Outro contato (opcional)</label>
      <input type="text" name="contato_extra" class="form-control" value="<?= htmlspecialchars($clinica['contato_extra']) ?>">
    </div>
    <button type="submit" class="btn btn-success">Salvar Alterações</button>
    <a href="manage_clinics.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include("includes/footer.php"); ?>
