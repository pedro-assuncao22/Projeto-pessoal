<?php
session_start();
require_once "includes/db.php";
include("includes/header.php");

$resultados = [];
$cidade = "";
$verTodas = false;

// quem pode editar/excluir?
$isPriv = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin','professional']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ver todas
    if (isset($_POST['ver_todas'])) {
        $verTodas = true;
        // se sua tabela não tiver 'nome', troque para 'name' aqui também
        $sql = "SELECT * FROM clinics ORDER BY cidade, nome";
        $res = $conn->query($sql);
        if ($res) {
            $resultados = $res->fetch_all(MYSQLI_ASSOC);
        }
    }
    // Buscar por cidade
    elseif (isset($_POST['cidade'])) {
        $cidade = trim($_POST['cidade']);
        if ($cidade !== "") {
            // se sua tabela não tiver 'nome', troque para 'name' aqui também
            $sql = "SELECT * FROM clinics WHERE cidade LIKE ? ORDER BY nome";
            $stmt = $conn->prepare($sql);
            $like = "%" . $cidade . "%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $res = $stmt->get_result();
            $resultados = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
}
?>

<div class="container my-4">
  <h2 class="mb-3">Clínicas de Atendimento</h2>


<?php
// Verifica se usuário é admin ou profissional verificado
$canAddClinic = false;

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        $canAddClinic = true;
    } elseif ($_SESSION['user_role'] === 'professional') {
        $userId = (int)$_SESSION['user_id'];
        $sqlProf = "SELECT verificado FROM professionals WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sqlProf);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $prof = $res->fetch_assoc()) {
            if ($prof['verificado'] == 1) {
                $canAddClinic = true;
            }
        }
        $stmt->close();
    }
}
?>

<?php if ($canAddClinic): ?>
  <div class="mb-3">
    <a href="add_clinic.php" class="btn btn-success">Cadastrar Nova Clínica</a>
  </div>
<?php endif; ?>






  <p>Digite o nome da cidade para encontrar clínicas de saúde mental cadastradas ou veja todas de uma vez.</p>

  <form method="post" class="row g-3 mb-3">
    <div class="col-md-6">
      <input type="text" name="cidade" class="form-control" placeholder="Ex.: São Paulo" value="<?= htmlspecialchars($cidade) ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Buscar</button>
    </div>
  </form>

  <form method="post" class="mb-3">
    <?php if (!$verTodas): ?>
      <button type="submit" name="ver_todas" value="1" class="btn btn-outline-success">Ver todas</button>
    <?php else: ?>
      <button type="button"
              onclick="document.getElementById('listaClinicas').style.display='none'; this.style.display='none'; document.getElementById('btnVerTodas').style.display='inline-block';"
              class="btn btn-outline-danger">Ocultar</button>
      <button type="submit" name="ver_todas" value="1" id="btnVerTodas" style="display:none;" class="btn btn-outline-success">Ver todas</button>
    <?php endif; ?>
  </form>

  <div id="listaClinicas" style="<?= $verTodas || !empty($cidade) ? '' : 'display:none;' ?>">
    <?php if (count($resultados) > 0): ?>
      <h4>Resultados encontrados:</h4>

      <?php foreach ($resultados as $clinica): ?>
        <?php
          // 🔐 Fallbacks de nomes de colunas para evitar undefined index
          $clinicId = (int)($clinica['id'] ?? $clinica['ID'] ?? $clinica['clinic_id'] ?? 0);
          $nome     = $clinica['nome'] ?? $clinica['name'] ?? 'Sem nome';
          $rua      = $clinica['rua'] ?? $clinica['address'] ?? '';
          $bairro   = $clinica['bairro'] ?? $clinica['district'] ?? '';
          $cidadeR  = $clinica['cidade'] ?? $clinica['city'] ?? '';
          $tel      = $clinica['telefone'] ?? $clinica['phone'] ?? '';
          $extra    = $clinica['contato_extra'] ?? $clinica['website'] ?? '';
        ?>

        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($nome) ?></h5>
            <?php if ($rua !== ''):    ?><p class="mb-1"><b>Rua:</b> <?= htmlspecialchars($rua) ?></p><?php endif; ?>
            <?php if ($bairro !== ''): ?><p class="mb-1"><b>Bairro:</b> <?= htmlspecialchars($bairro) ?></p><?php endif; ?>
            <?php if ($cidadeR !== ''):?><p class="mb-1"><b>Cidade:</b> <?= htmlspecialchars($cidadeR) ?></p><?php endif; ?>
            <?php if ($tel !== ''):    ?><p class="mb-1"><b>Telefone:</b> <?= htmlspecialchars($tel) ?></p><?php endif; ?>
            <?php if ($extra !== ''):  ?><p class="mb-1"><b>Outro contato:</b> <?= htmlspecialchars($extra) ?></p><?php endif; ?>

            <!-- Botões somente para admin/professional e se tivermos um ID válido -->
            <?php if ($isPriv && $clinicId > 0): ?>
              <div class="mt-2">
                <a href="edit_clinic.php?id=<?= $clinicId ?>" class="btn btn-sm btn-warning">Editar</a>
                <form method="post" action="manage_clinics.php" style="display:inline-block;"
                      onsubmit="return confirm('Deseja realmente excluir esta clínica?');">
                  <input type="hidden" name="delete_id" value="<?= $clinicId ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="alert alert-warning">Nenhuma clínica encontrada.</div>
    <?php endif; ?>
  </div>
</div>

<?php include("includes/footer.php"); ?>
