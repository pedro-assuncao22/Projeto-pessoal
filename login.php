<?php
// login.php
// Processamento antes do HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "includes/db.php"; // ajuste o caminho se necessário

// inicializa variáveis (evita "undefined variable")
$error = "";

// Processa POST do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email === "" || $password === "") {
        $error = "Preencha email e senha.";
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();
                $stored = $user['password'];

                // Se senha armazenada for hash
                if (password_verify($password, $stored)) {
                    // sucesso: cria sessão
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    header("Location: index.php");
                    exit;
                }

                // Fallback para senhas em texto puro (legado):
                // compara diretamente e, se bater, re-hash e atualiza o DB
                if ($password === $stored) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($upd) {
                        $upd->bind_param("si", $newHash, $user['id']);
                        $upd->execute();
                    }
                    // Loga o usuário
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    header("Location: index.php");
                    exit;
                }

                // se chegou aqui, senha incorreta
                $error = "Email ou senha incorretos.";
            } else {
                $error = "Usuário não encontrado.";
            }
            $stmt->close();
        } else {
            $error = "Erro no servidor (prepare falhou). Tente novamente.";
        }
    }
}

// inclui o header (após processamento)
include("includes/header.php");
?>

<!-- HTML: login compacto e centralizado -->
<div class="d-flex justify-content-center align-items-center" style="min-height: 75vh;">
  <div class="card shadow-lg" style="max-width: 420px; width: 100%;">
    <div class="card-body p-4">
      <h3 class="card-title text-center mb-3">Entrar</h3>

      <?php if ($error): ?>
        <div class="alert alert-danger small mb-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
      </form>

      <div class="mt-3 text-center small">
        Ainda não tem conta? <a href="register.php">Cadastre-se</a>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
