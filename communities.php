<?php include("includes/header.php");

include("auth.php");

$sql = "SELECT * FROM communities ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Comunidades</h2>
  
  <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="add_community.php" class="btn btn-success">+ Criar Comunidade</a>
  <?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
  <div class="alert alert-success">Comunidade excluída com sucesso!</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
  <div class="alert alert-danger">Erro ao excluir comunidade.</div>
<?php endif; ?>

<?php while($row = $result->fetch_assoc()): ?>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
      <p class="card-text"><?= nl2br(htmlspecialchars($row['description'])) ?></p>

      <?php if (!empty($row['link'])): ?>
        <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
          Entrar na Comunidade
        </a>
      <?php endif; ?>

      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="delete_community.php?id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-outline-danger"
           onclick="return confirm('Tem certeza que deseja excluir esta comunidade?');">
          Excluir
        </a>
      <?php endif; ?>
    </div>
  </div>
<?php endwhile; ?>

<?php include("includes/footer.php"); ?>