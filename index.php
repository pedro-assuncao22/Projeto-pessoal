<?php include("includes/header.php"); 


// se não estiver logado → manda para login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<div class="text-center p-5 bg-white rounded shadow-sm">
  <h1 class="text-primary">Bem-vindo ao Portal de Saúde Mental</h1>
  <p class="lead">Encontre apoio, reflexões e comunidades para cuidar da sua mente.</p>
  <a href="reflections.php" class="btn btn-outline-primary m-2">Reflexões</a>
  <a href="communities.php" class="btn btn-outline-success m-2">Comunidades</a>
  <a href="clinics.php" class="btn btn-outline-dark m-2">Clínicas</a>
  <a href="chat_hub.php" class="btn btn-outline-success m-2">Chats</a>
  <a href="colaboracoes.php" class="btn btn-outline-primary m-2">Colaborações</a>
</div>

<?php include("includes/footer.php"); ?>
