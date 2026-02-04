<?php
// professionals.php (versão revisada)
if (session_status() === PHP_SESSION_NONE) session_start();

// --- DEV: habilite saída de erros enquanto debuga (remova em produção) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";

$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

$error = "";
$professionals = [];

// Busca profissionais (admins veem todos, usuários só verificados)
try {
    if ($isAdmin) {
        $sql = "SELECT p.*, u.name AS user_name FROM professionals p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Erro ao preparar consulta: " . $conn->error);
        $stmt->execute();
        $res = $stmt->get_result();
        $professionals = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    } else {
        $sql = "SELECT p.*, u.name AS user_name FROM professionals p JOIN users u ON p.user_id = u.id WHERE p.verificado = 1 ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Erro ao preparar consulta: " . $conn->error);
        $stmt->execute();
        $res = $stmt->get_result();
        $professionals = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include("includes/header.php");
?>

<div class="container my-4">
  <h2 class="mb-4">Profissionais</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($isAdmin): ?>
    <div class="mb-3">
      <a href="manage_professionals.php" class="btn btn-sm btn-outline-primary">Gerenciar Profissionais</a>
    </div>
  <?php endif; ?>

  <?php if (empty($professionals)): ?>
    <div class="alert alert-info">Nenhum profissional encontrado.</div>
  <?php endif; ?>

  <?php foreach ($professionals as $row): ?>
    <?php $prof_id = intval($row['id']); ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title mb-1"><?= htmlspecialchars($row['user_name'] ?? '—') ?></h5>
            <small class="text-muted">Cadastrado em <?= htmlspecialchars($row['created_at'] ?? '—') ?></small>
          </div>

          <div>
            <?php if ($isAdmin): ?>
              <?php if (empty($row['verificado'])): ?>
                <a href="manage_professionals.php?verify=<?= $prof_id ?>" class="btn btn-sm btn-success me-1"
                   onclick="return confirm('Confirmar verificação deste profissional?')">Verificar</a>
              <?php else: ?>
                <span class="badge bg-success me-1">Verificado</span>
              <?php endif; ?>

              <a href="delete_professional.php?id=<?= $prof_id ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Excluir este cadastro de profissional?')">Excluir</a>
            <?php else: ?>
              <?php if (!empty($row['verificado'])): ?>
                <span class="badge bg-success">Verificado</span>
              <?php else: ?>
                <span class="badge bg-secondary">Não verificado</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <hr>
        <p><b>Formação:</b> <?= htmlspecialchars($row['formacao'] ?? '') ?></p>

        <p><b>CRM / CRP:</b> <?= htmlspecialchars($row['crm'] ?? '') ?></p>



        <?php if (!empty($row['experiencia'])): ?>
          <p><b>Experiência:</b><br><?= nl2br(htmlspecialchars($row['experiencia'])) ?></p>
        <?php endif; ?>

        <p><b>Contato:</b> <?= htmlspecialchars($row['contato'] ?? '') ?></p>
        <?php if (!empty($row['contato_extra'])): ?>
          <p><b>Outro contato:</b> <?= htmlspecialchars($row['contato_extra']) ?></p>
        <?php endif; ?>

        <hr>
        <h6>Feedbacks</h6>

        <?php
        // Busca feedbacks de forma segura usando variável para bind_param
        $sqlF = "SELECT f.*, u.name AS user_name FROM feedbacks f JOIN users u ON f.user_id = u.id WHERE f.professional_id = ? ORDER BY f.created_at DESC";
        $stmtF = $conn->prepare($sqlF);
        if ($stmtF) {
            $stmtF->bind_param("i", $prof_id);
            $stmtF->execute();
            $resF = $stmtF->get_result();
            if ($resF && $resF->num_rows > 0) {
                while ($fb = $resF->fetch_assoc()) {
                    echo "<div class='mb-2'><b>" . htmlspecialchars($fb['user_name']) . ":</b> "
                        . nl2br(htmlspecialchars($fb['comentario']))
                        . "<br><small class='text-muted'>" . $fb['created_at'] . "</small></div>";
                }
            } else {
                echo "<p class='text-muted small'>Ainda não há feedbacks.</p>";
            }
            $stmtF->close();
        } else {
            echo "<p class='text-danger small'>Erro ao buscar feedbacks.</p>";
        }
        ?>

        <?php if (isset($_SESSION['user_id'])): ?>
          <form method="post" action="send_feedback.php" class="mt-3">
            <input type="hidden" name="professional_id" value="<?= $prof_id ?>">
            <div class="mb-2">
              <textarea name="comentario" class="form-control" placeholder="Deixe um feedback sobre a consulta..." required rows="2"></textarea>
            </div>
            <button class="btn btn-sm btn-outline-primary">Enviar feedback</button>
          </form>
        <?php else: ?>
          <p class="small text-muted mt-2">Faça login para enviar feedback.</p>
        <?php endif; ?>

      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include("includes/footer.php"); ?>
