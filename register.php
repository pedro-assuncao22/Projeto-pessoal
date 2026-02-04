<?php
session_start();
require_once "includes/db.php"; // garante $conn

// Inicializa variáveis
$erro = "";
$sucesso = "";

// Valores para re-popular o formulário em caso de erro
$old = [
    'name' => '',
    'email' => '',
    'is_prof' => '',
    'formacao' => '',
    'crm' => '',
    'experiencia' => '',
    'contato' => '',
    'contato_extra' => ''
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // pega valores com fallback seguro
    $nome  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? '';

    // guarda para re-popular caso dê erro
    $old['name'] = $nome;
    $old['email'] = $email;
    $old['is_prof'] = isset($_POST['is_prof']) ? '1' : '';
    $old['formacao'] = trim($_POST['formacao'] ?? '');
    $old['crm'] = trim($_POST['crm'] ?? '');
    $old['experiencia'] = trim($_POST['experiencia'] ?? '');
    $old['contato'] = trim($_POST['contato'] ?? '');
    $old['contato_extra'] = trim($_POST['contato_extra'] ?? '');

    // Validações básicas
    if ($nome === '' || $email === '' || $senha === '') {
        $erro = "Preencha nome, email e senha.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        // Verifica se email já existe
        $sqlCheck = "SELECT id FROM users WHERE email = ?";
        if ($stmtCheck = $conn->prepare($sqlCheck)) {
            $stmtCheck->bind_param("s", $email);
            $stmtCheck->execute();
            $res = $stmtCheck->get_result();
            if ($res && $res->num_rows > 0) {
                $erro = "Este e-mail já está em uso.";
            }
            $stmtCheck->close();
        } else {
            $erro = "Erro interno (checagem de email).";
        }

        // Se nenhuma falha até aqui, insere usuário
        if ($erro === "") {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $sqlUser = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
            if ($stmtUser = $conn->prepare($sqlUser)) {
                $stmtUser->bind_param("sss", $nome, $email, $senhaHash);
                if ($stmtUser->execute()) {
                    $user_id = $conn->insert_id; // id do novo usuário

                    // Se marcou como profissional, valida e salva em professionals
                    if (isset($_POST['is_prof']) && $_POST['is_prof'] == '1') {
                        $formacao = $old['formacao'];
                        $crm = $old['crm'];
                        $experiencia = $old['experiencia'];
                        $contato = $old['contato'];
                        $contato_extra = $old['contato_extra'];

                        // Exigir formação, CRM e contato mínimo
                        if ($formacao === '' || $crm === '' || $contato === '') {
                            $conn->query("DELETE FROM users WHERE id = " . intval($user_id));
                            $erro = "Se você escolheu 'Sou profissional', informe pelo menos formação, CRM e contato.";
                        } else {
                            $sqlProf = "INSERT INTO professionals (user_id, formacao, crm, experiencia, contato, contato_extra, verificado) 
                                        VALUES (?, ?, ?, ?, ?, ?, 0)";
                            if ($stmtProf = $conn->prepare($sqlProf)) {
                                $contato_extra_param = $contato_extra === '' ? null : $contato_extra;
                                $stmtProf->bind_param("isssss", $user_id, $formacao, $crm, $experiencia, $contato, $contato_extra_param);
                                if (!$stmtProf->execute()) {
                                    $conn->query("DELETE FROM users WHERE id = " . intval($user_id));
                                    $erro = "Erro ao salvar dados de profissional. Tente novamente.";
                                }
                                $stmtProf->close();
                            } else {
                                $conn->query("DELETE FROM users WHERE id = " . intval($user_id));
                                $erro = "Erro interno (preparing insert professional).";
                            }
                        }
                    }

                    // se chegou até aqui sem erro, sucesso
                    if ($erro === "") {
                        $sucesso = "Conta criada com sucesso! Você já pode fazer login.";
                        // limpa old values
                        $old = array_map(function(){ return ''; }, $old);
                    }
                } else {
                    $erro = "Erro ao cadastrar usuário. Tente novamente.";
                }
                $stmtUser->close();
            } else {
                $erro = "Erro interno (prepare insert user).";
            }
        }
    }
}
?>

<?php include("includes/header.php"); ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm p-4">
      <h3 class="text-center text-success mb-3">Cadastro</h3>

      <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <div class="text-center">
          <a href="login.php" class="btn btn-primary">Ir para Login</a>
        </div>
      <?php else: ?>
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($old['name']) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($old['email']) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required minlength="6">
            <div class="form-text">A senha deve ter no mínimo 6 caracteres.</div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_prof" name="is_prof" value="1" <?= $old['is_prof'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_prof">
              Sou psicólogo(a) ou psicoterapeuta (Profissional de Saúde Mental)
            </label>
          </div>

          <div id="profFields" style="display:<?= $old['is_prof'] ? 'block' : 'none' ?>;">
            <div class="mb-3">
              <label class="form-label">Formação</label>
              <input type="text" name="formacao" class="form-control" value="<?= htmlspecialchars($old['formacao']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">CRM / CRP</label>
              <input type="text" name="crm" class="form-control" value="<?= htmlspecialchars($old['crm']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Experiência</label>
              <textarea name="experiencia" rows="3" class="form-control"><?= htmlspecialchars($old['experiencia']) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Número de Contato</label>
              <input type="text" name="contato" class="form-control" value="<?= htmlspecialchars($old['contato']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Outro contato (opcional)</label>
              <input type="text" name="contato_extra" class="form-control" value="<?= htmlspecialchars($old['contato_extra']) ?>">
            </div>
          </div>

          <button type="submit" class="btn btn-success w-100">Cadastrar</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
  var isProfCheckbox = document.getElementById("is_prof");
  var profFields = document.getElementById("profFields");
  if (!isProfCheckbox || !profFields) return;

  isProfCheckbox.addEventListener("change", function() {
    profFields.style.display = this.checked ? "block" : "none";
  });
});
</script>
