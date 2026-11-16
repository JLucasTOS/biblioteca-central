<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('pedidos.php');validar_csrf();
$id=(int)($_POST['id']??0);$decisao=$_POST['decisao']??'';$obs=trim($_POST['observacao']??'');
if(!in_array($decisao,['aprovar','recusar'],true)||mb_strlen($obs)>500)redirecionar('pedidos.php');
$funcionario=usuario_logado()['id_funcionario'];
if($decisao==='recusar'){
 $st=$pdo->prepare("UPDATE pedido_emprestimo SET status='recusado',observacao=?,analisado_em=NOW(),id_funcionario=? WHERE id_pedido=? AND status='pendente'");$st->execute([$obs?:null,$funcionario,$id]);
 mensagem($st->rowCount()?'sucesso':'erro',$st->rowCount()?'Solicitação recusada.':'Pedido já analisado.');redirecionar('pedidos.php');
}
// Reserva atômica do pedido. A procedure de empréstimo tem transação própria.
$st=$pdo->prepare("UPDATE pedido_emprestimo SET status='processando' WHERE id_pedido=? AND status='pendente'");$st->execute([$id]);
if(!$st->rowCount()){mensagem('erro','Este pedido já foi analisado.');redirecionar('pedidos.php');}
try{
 $st=$pdo->prepare('SELECT id_usuario,id_livro FROM pedido_emprestimo WHERE id_pedido=?');$st->execute([$id]);$p=$st->fetch();
 $st=$pdo->prepare('CALL emprestar_livro(?,?,?)');$st->execute([$p['id_usuario'],$p['id_livro'],$funcionario]);
 $res=$st->fetch();$st->closeCursor();$emprestimo=(int)($res['id_emprestimo_criado']??0);
 if(!$emprestimo)throw new RuntimeException('A procedure não retornou o número do empréstimo.');
 $st=$pdo->prepare("UPDATE pedido_emprestimo SET status='aprovado',id_emprestimo=?,id_funcionario=?,observacao=?,analisado_em=NOW() WHERE id_pedido=? AND status='processando'");$st->execute([$emprestimo,$funcionario,$obs?:null,$id]);
 mensagem('sucesso','Pedido aprovado. Empréstimo #'.$emprestimo.' registrado.');
}catch(Throwable $e){
 // Caso a gravação final falhe, não se deve repetir automaticamente a procedure.
 // Verifique manualmente o empréstimo antes de decidir sobre o pedido em processamento.
 mensagem('erro','Falha ao processar aprovação. Confira o empréstimo no painel e o pedido em status processando antes de tentar qualquer correção.');
}
redirecionar('pedidos.php?status=todos');
