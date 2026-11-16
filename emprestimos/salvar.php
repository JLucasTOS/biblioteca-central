<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('index.php'); validar_csrf();
$usuario=(int)($_POST['id_usuario']??0); $livro=(int)($_POST['id_livro']??0);
$funcionario=($_POST['id_funcionario']??'')!==''?(int)$_POST['id_funcionario']:null;
try{
 $st=$pdo->prepare("CALL emprestar_livro(?,?,?)"); $st->execute([$usuario,$livro,$funcionario]);
 $resultado=$st->fetch(); $st->closeCursor();
 mensagem('sucesso','Empréstimo registrado. Número: '.(int)($resultado['id_emprestimo_criado']??0));
}catch(PDOException $e){
 $msg=$e->getMessage();
 if(str_contains($msg,'indisponível')) $texto='Este livro não possui exemplares disponíveis.';
 elseif(str_contains($msg,'já está com este livro')) $texto='Este leitor já está com este livro emprestado.';
 elseif(str_contains($msg,'Usuário não encontrado')) $texto='Leitor não encontrado ou inativo.';
 else $texto='Não foi possível registrar o empréstimo. Confira os dados e tente novamente.';
 mensagem('erro',$texto);
}
redirecionar('index.php');
