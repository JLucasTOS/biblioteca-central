<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'leitor');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('index.php');validar_csrf();
$id=(int)($_POST['id_livro']??0);$usuario=usuario_logado()['id_usuario'];
try{
 $pdo->beginTransaction();
 // Serializa solicitações deste leitor para o mesmo título.
 $st=$pdo->prepare('SELECT id_usuario FROM usuario WHERE id_usuario=? FOR UPDATE');$st->execute([$usuario]);
 $st=$pdo->prepare('SELECT id_livro FROM livro WHERE id_livro=? AND ativo=1');$st->execute([$id]);if(!$st->fetch()) throw new RuntimeException('Livro não encontrado.');
 $st=$pdo->prepare('SELECT disponivel FROM vw_livro_disponivel WHERE id_livro=?');$st->execute([$id]);if((int)$st->fetchColumn()<=0) throw new RuntimeException('Livro indisponível no momento.');
 $st=$pdo->prepare("SELECT COUNT(*) FROM pedido_emprestimo WHERE id_usuario=? AND id_livro=? AND status IN ('pendente','processando')");$st->execute([$usuario,$id]);if((int)$st->fetchColumn()>0) throw new RuntimeException('Você já solicitou este livro.');
 $st=$pdo->prepare('SELECT COUNT(*) FROM emprestimo e JOIN item_emprestimo i ON i.id_emprestimo=e.id_emprestimo WHERE e.id_usuario=? AND i.id_livro=? AND e.data_devolucao IS NULL');$st->execute([$usuario,$id]);if((int)$st->fetchColumn()>0) throw new RuntimeException('Você já está com este livro emprestado.');
 $pdo->prepare('INSERT INTO pedido_emprestimo(id_usuario,id_livro) VALUES(?,?)')->execute([$usuario,$id]);
 $pdo->commit();mensagem('sucesso','Solicitação enviada! Aguarde a análise da biblioteca.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();mensagem('erro',$e instanceof RuntimeException?$e->getMessage():'Não foi possível registrar seu pedido.');}
redirecionar('pedidos.php');
