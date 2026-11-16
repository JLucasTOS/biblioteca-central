<?php
declare(strict_types=1);
require_once __DIR__ . '/funcoes.php';

function usuario_logado(): ?array { return $_SESSION['auth'] ?? null; }
function exigir_login(?string $perfil = null): void {
 $auth = usuario_logado();
 if (!$auth) { mensagem('erro', 'Faça login para continuar.'); redirecionar('/biblioteca_central_sistema/login.php'); }
 if ($perfil !== null && $auth['perfil'] !== $perfil) { http_response_code(403); exit('Acesso negado para este perfil.'); }
}
function login_conta(array $conta): void {
 session_regenerate_id(true);
 $_SESSION['auth'] = ['id_conta'=>(int)$conta['id_conta'], 'perfil'=>$conta['perfil'],
   'id_usuario'=>isset($conta['id_usuario'])?(int)$conta['id_usuario']:null,
   'id_funcionario'=>isset($conta['id_funcionario'])?(int)$conta['id_funcionario']:null,
   'email'=>$conta['email']];
}
function conta_ativa(PDO $pdo): bool {
 $auth = usuario_logado(); if (!$auth) return false;
 $st=$pdo->prepare('SELECT ativo,perfil,id_usuario,id_funcionario FROM conta WHERE id_conta=?');
 $st->execute([$auth['id_conta']]); $c=$st->fetch();
 if (!$c || !(int)$c['ativo'] || $c['perfil']!==$auth['perfil']) return false;
 if ($c['perfil']==='leitor') {
  $st=$pdo->prepare('SELECT ativo FROM usuario WHERE id_usuario=?');$st->execute([$auth['id_usuario']]);
  return (bool)$st->fetchColumn();
 }
 if ($auth['id_funcionario'] !== null) {
  $st=$pdo->prepare('SELECT ativo FROM funcionario WHERE id_funcionario=?');$st->execute([$auth['id_funcionario']]);
  return (bool)$st->fetchColumn();
 }
 return true;
}
function exigir_conta_ativa(PDO $pdo, string $perfil): void {
 exigir_login($perfil);
 if (!conta_ativa($pdo)) { unset($_SESSION['auth']); mensagem('erro','Conta desativada.'); redirecionar('/biblioteca_central_sistema/login.php'); }
}
