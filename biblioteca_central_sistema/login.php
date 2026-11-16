<?php
require_once __DIR__.'/config/conexao.php';
require_once __DIR__.'/includes/auth.php';
if (usuario_logado()) redirecionar(usuario_logado()['perfil']==='admin'?'index.php':'leitor/index.php');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 validar_csrf();
 $email=mb_strtolower(trim($_POST['email']??'')); $senha=$_POST['senha']??'';
 $st=$pdo->prepare('SELECT * FROM conta WHERE email=? AND ativo=1');$st->execute([$email]);$conta=$st->fetch();
 if ($conta && password_verify($senha,$conta['senha_hash'])) {
  login_conta($conta);
  if (!conta_ativa($pdo)) {unset($_SESSION['auth']);mensagem('erro','Conta ou cadastro desativado.');redirecionar('login.php');}
  redirecionar($conta['perfil']==='admin'?'index.php':'leitor/index.php');
 }
 mensagem('erro','E-mail ou senha inválidos.');redirecionar('login.php');
}
?>
<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar | Biblioteca Central</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-page">
<main class="auth-card"><div class="auth-brand">B <span>Biblioteca Central</span></div><span class="eyebrow">ACESSO AO SISTEMA</span><h1>Bem-vindo de volta</h1><p class="muted">Leitores e administradores entram com suas próprias contas.</p>
<?php mostrar_mensagem(); ?>
<form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="field">E-mail<input type="email" name="email" autocomplete="username" required></label><label class="field">Senha<input type="password" name="senha" autocomplete="current-password" required></label><button class="button button-primary">Entrar</button></form>
<p class="auth-footer">Ainda não tem cadastro? <a href="cadastro.php">Criar conta de leitor</a></p></main></body></html>
