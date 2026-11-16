<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('index.php'); validar_csrf();
$id=(int)($_POST['id']??0);
try{$st=$pdo->prepare("CALL devolver_emprestimo(?)");$st->execute([$id]);$st->closeCursor();$pdo->prepare("UPDATE pedido_devolucao SET status='confirmado',analisado_em=NOW(),id_conta_admin=? WHERE id_emprestimo=? AND status='pendente'")->execute([(int)usuario_logado()['id_conta'],$id]);mensagem('sucesso','Devolução registrada com sucesso.');}
catch(PDOException $e){mensagem('erro','Não foi possível registrar a devolução. Talvez o empréstimo já tenha sido devolvido.');}
redirecionar('index.php');
