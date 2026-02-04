<?php
session_start();
require_once "includes/db.php";

// Permitir apenas admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem acessar esta página.");
}

$erro = "";
$sucesso = "";

// Se excluir foi solicitado
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $sql = "DELETE FROM clinics WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $sucesso = "Clínica excluída com sucesso!";
        } else {
            $erro = "Erro ao excluir: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $erro = "Erro interno: " . $conn->error;
    }
}

// Buscar todas as clínicas
$sql = "SELECT * FROM clinics ORDER BY cidade, nome";
$res = $conn->query($sql);
$clinicas = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

include("includes/header.php");
?>

<div class="container my-4">
  <h2 class="mb-3">Gerenciar Clínicas</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>
  <?php if ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <?php if (count($clinicas) > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Rua</th>
          <th>Bairro</th>
          <th>Cidade</th>
          <th>Telefone</th>
          <th>Contato extra</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clinicas as $clinica): ?>



<td>
  <?php if ($_SESSION['user_role'] === 'admin'): ?>
    <a href="edit_clinic.php?id=<?= $clinica['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
    <form method="post" style="display:inline-block;" onsubmit="return confirm('Deseja realmente excluir esta clínica?');">
      <input type="hidden" name="delete_id" value="<?= $clinica['id'] ?>">
      <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
    </form>
  <?php else: ?>
    <span class="text-muted">Acesso restrito</span>
  <?php endif; ?>
</td>








          
              <a href="edit_clinic.php?id=<?= $clinica['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
              <form method="post" style="display:inline-block;" onsubmit="return confirm('Deseja realmente excluir esta clínica?');">
                <input type="hidden" name="delete_id" value="<?= $clinica['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">Nenhuma clínica cadastrada ainda.</div>
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
