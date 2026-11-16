<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('devolucoes.php');validar_csrf();
$id=(int)($_POST['id']??0);$decisao=$_POST['decisao']??'';$obs=trim($_POST['observacao']??'');
if(!in_array($decisao,['confirmar','recusar'],true)||mb_strlen($obs)>500) redirecionar('devolucoes.php');
try {
 $pdo->beginTransaction();
 $st=$pdo->prepare('SELECT id_emprestimo,status FROM pedido_devolucao WHERE id_pedido_devolucao=? FOR UPDATE');$st->execute([$id]);$pedido=$st->fetch();
 if(!$pedido || $pedido['status']!=='pendente') throw new RuntimeException('Pedido já analisado ou inexistente.');
 if($decisao==='confirmar') {
  $st=$pdo->prepare('UPDATE emprestimo SET data_devolucao=CURRENT_DATE WHERE id_emprestimo=? AND data_devolucao IS NULL');$st->execute([(int)$pedido['id_emprestimo']]);
  if($st->rowCount()!==1) throw new RuntimeException('Empréstimo já devolvido.');
  $status='confirmado';
 } else $status='recusado';
 $st=$pdo->prepare('UPDATE pedido_devolucao SET status=?,analisado_em=NOW(),observacao=?,id_conta_admin=? WHERE id_pedido_devolucao=?');$st->execute([$status,$obs?:null,(int)usuario_logado()['id_conta'],$id]);
 $pdo->commit();mensagem('sucesso',$status==='confirmado'?'Devolução confirmada; exemplar liberado.':'Pedido recusado.');
} catch(Throwable $e) {if($pdo->inTransaction()) $pdo->rollBack();mensagem('erro',$e instanceof RuntimeException?$e->getMessage():'Falha na análise.');}
redirecionar('devolucoes.php');
