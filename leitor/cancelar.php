<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'leitor');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('pedidos.php');validar_csrf();
$st=$pdo->prepare("UPDATE pedido_emprestimo SET status='cancelado',analisado_em=NOW() WHERE id_pedido=? AND id_usuario=? AND status='pendente'");$st->execute([(int)($_POST['id']??0),usuario_logado()['id_usuario']]);
mensagem($st->rowCount()?'sucesso':'erro',$st->rowCount()?'Pedido cancelado.':'Este pedido não pode mais ser cancelado.');redirecionar('pedidos.php');
