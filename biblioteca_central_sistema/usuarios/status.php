<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('index.php'); validar_csrf();
$st=$pdo->prepare("UPDATE usuario SET ativo=IF(ativo=1,0,1) WHERE id_usuario=?");$st->execute([(int)($_POST['id']??0)]);
mensagem('sucesso','Status do leitor atualizado.');redirecionar('index.php');
