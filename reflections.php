<?php 
include("includes/header.php");
include("auth.php");

// Buscar últimas reflexões
$sql = "SELECT * FROM reflections ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);

// Verifica se usuário pode gerenciar reflexões
$canManage = false;
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        $canManage = true;
    } elseif ($_SESSION['user_role'] === 'professional') {
        $userId = (int)$_SESSION['user_id'];
        $sqlProf = "SELECT verificado FROM professionals WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sqlProf);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resProf = $stmt->get_result();
        if ($resProf && $prof = $resProf->fetch_assoc()) {
            if ($prof['verificado'] == 1) {
                $canManage = true;
            }
        }
        $stmt->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Reflexões</h2>
  
  <?php if ($canManage): ?>
    <a href="add_reflection.php" class="btn btn-primary">+ Adicionar Reflexão</a>
  <?php endif; ?>
</div>

<?php while($row = $result->fetch_assoc()): ?>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
      <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
      <small class="text-muted">Publicado em <?= $row['created_at'] ?></small>

      <?php if ($canManage): ?>
        <div class="mt-2">
          <a href="delete_reflection.php?id=<?= $row['id'] ?>" 
             class="btn btn-sm btn-danger"
             onclick="return confirm('Deseja realmente excluir esta reflexão?');">
             Excluir
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endwhile; ?>

<?php include("includes/footer.php"); ?>
