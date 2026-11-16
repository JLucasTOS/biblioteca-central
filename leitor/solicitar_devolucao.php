<?php
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/auth.php';
exigir_conta_ativa($pdo,'leitor');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('pedidos.php');
validar_csrf();
$id=(int)($_POST['id_emprestimo']??0);
$usuario=(int)usuario_logado()['id_usuario'];
try {
 $pdo->beginTransaction();
 $st=$pdo->prepare('SELECT id_emprestimo FROM emprestimo WHERE id_emprestimo=? AND id_usuario=? AND data_devolucao IS NULL FOR UPDATE');
 $st->execute([$id,$usuario]);
 if(!$st->fetch()) throw new RuntimeException('Empréstimo não encontrado ou já devolvido.');
 $st=$pdo->prepare("SELECT COUNT(*) FROM pedido_devolucao WHERE id_emprestimo=? AND status='pendente'");
 $st->execute([$id]);
 if((int)$st->fetchColumn()>0) throw new RuntimeException('Já existe um pedido de devolução pendente.');
 $pdo->prepare('INSERT INTO pedido_devolucao(id_emprestimo,id_usuario) VALUES(?,?)')->execute([$id,$usuario]);
 $pdo->commit();
 mensagem('sucesso','Devolução solicitada. Entregue o livro à biblioteca para confirmação.');
} catch(Throwable $e) {
 if($pdo->inTransaction()) $pdo->rollBack();
 mensagem('erro',$e instanceof RuntimeException?$e->getMessage():'Não foi possível solicitar a devolução.');
}
redirecionar('pedidos.php');
