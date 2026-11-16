<?php
require_once __DIR__.'/config/conexao.php';require_once __DIR__.'/includes/auth.php';
if(usuario_logado()) redirecionar('index.php');
if($_SERVER['REQUEST_METHOD']==='POST'){
 validar_csrf();$nome=trim($_POST['nome']??'');$email=mb_strtolower(trim($_POST['email']??''));$senha=$_POST['senha']??'';
 if(mb_strlen($nome)<3 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($senha)<10){mensagem('erro','Informe nome, e-mail válido e senha de pelo menos 10 caracteres.');redirecionar('cadastro.php');}
 try{
  $pdo->beginTransaction();
  $st=$pdo->prepare('SELECT id_usuario FROM usuario WHERE email=?');$st->execute([$email]);
  if($st->fetch()) throw new RuntimeException('Este e-mail já existe no cadastro da biblioteca. Peça à administração para vincular uma conta.');
  $st=$pdo->prepare('INSERT INTO usuario(nome,email,ativo) VALUES(?,?,1)');$st->execute([$nome,$email]);$id=(int)$pdo->lastInsertId();
  $st=$pdo->prepare("INSERT INTO conta(email,senha_hash,perfil,id_usuario) VALUES(?,?,'leitor',?)");$st->execute([$email,password_hash($senha,PASSWORD_DEFAULT),$id]);
  $pdo->commit();mensagem('sucesso','Cadastro realizado! Entre com seu e-mail e senha.');redirecionar('login.php');
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();mensagem('erro',$e instanceof RuntimeException?$e->getMessage():'Não foi possível cadastrar. Verifique se o e-mail já está em uso.');redirecionar('cadastro.php');}
}
?>
<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Criar conta | Biblioteca Central</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-page"><main class="auth-card"><div class="auth-brand">B <span>Biblioteca Central</span></div><span class="eyebrow">PORTAL DO LEITOR</span><h1>Crie sua conta</h1><p class="muted">Cadastre-se para solicitar empréstimos de livros.</p><?php mostrar_mensagem(); ?>
<form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="field">Nome completo<input name="nome" required minlength="3" maxlength="150"></label><label class="field">E-mail<input name="email" type="email" required autocomplete="email"></label><label class="field">Senha (mínimo 10 caracteres)<input name="senha" type="password" required minlength="10" autocomplete="new-password"></label><button class="button button-primary">Criar conta</button></form><p class="auth-footer">Já tem conta? <a href="login.php">Entrar</a></p></main></body></html>
