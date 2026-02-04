<?php
session_start();
require_once "includes/db.php";
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado.");
}

// Verificar
if (isset($_GET['verify'])) {
    $id = intval($_GET['verify']);
    $upd = $conn->prepare("UPDATE professionals SET verificado = 1 WHERE id = ?");
    $upd->bind_param("i", $id);
    $upd->execute();
    header("Location: manage_professionals.php");
    exit;
}

// Listagem
$sql = "SELECT p.*, u.name AS user_name 
        FROM professionals p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.id DESC";
$res = $conn->query($sql);

include("includes/header.php");
?>
<h2 class="mb-4">Gerenciar Profissionais</h2>

<?php while($row = $res->fetch_assoc()): ?>
  <div class="card mb-3 shadow-sm p-3">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h5 class="mb-1"><?= htmlspecialchars($row['user_name']) ?></h5>
        <p class="mb-1"><b>Formação:</b> <?= htmlspecialchars($row['formacao']) ?></p>
        <?php if (!empty($row['experiencia'])): ?>
          <p class="mb-1"><b>Experiência:</b> <?= nl2br(htmlspecialchars($row['experiencia'])) ?></p>
        <?php endif; ?>
        <p class="mb-1"><b>Contato:</b> <?= htmlspecialchars($row['contato']) ?></p>
        <?php if (!empty($row['contato_extra'])): ?>
          <p class="mb-1"><b>Outro contato:</b> <?= htmlspecialchars($row['contato_extra']) ?></p>
        <?php endif; ?>
      </div>

      <div class="text-end">
        <?php if (!$row['verificado']): ?>
          <a class="btn btn-sm btn-success mb-1" href="?verify=<?= $row['id'] ?>" onclick="return confirm('Confirmar verificação deste profissional?');">Verificar</a>
        <?php else: ?>
          <span class="badge bg-success d-block mb-1">Verificado</span>
        <?php endif; ?>
        <a class="btn btn-sm btn-danger" href="delete_professional.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir este profissional?')">Excluir</a>
      </div>
    </div>
  </div>
<?php endwhile; ?>

<?php include("includes/footer.php"); ?>
