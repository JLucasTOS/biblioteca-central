<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('index.php'); validar_csrf();
$id=(int)($_POST['id']??0); $nome=trim($_POST['nome']??''); $email=trim($_POST['email']??'');
$telefone=trim($_POST['telefone']??''); $nascimento=$_POST['data_nascimento']?:null;
if($nome==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)){mensagem('erro','Informe nome e e-mail válidos.');redirecionar($id?"editar.php?id=$id":'cadastrar.php');}
try{
 if($id){$check=$pdo->prepare('SELECT COUNT(*) FROM conta WHERE id_usuario=? AND email<>?');$check->execute([$id,$email]);if((int)$check->fetchColumn()>0)throw new RuntimeException('E-mail de leitor com conta não pode ser alterado por este formulário.');$st=$pdo->prepare("UPDATE usuario SET nome=?,email=?,telefone=?,data_nascimento=? WHERE id_usuario=?");$st->execute([$nome,$email,$telefone?:null,$nascimento,$id]);}
 else{$st=$pdo->prepare("INSERT INTO usuario(nome,email,telefone,data_nascimento) VALUES(?,?,?,?)");$st->execute([$nome,$email,$telefone?:null,$nascimento]);}
 mensagem('sucesso',$id?'Leitor atualizado.':'Leitor cadastrado.');
}catch(PDOException $e){mensagem('erro',$e instanceof RuntimeException?$e->getMessage():'Não foi possível salvar. Verifique se o e-mail já está cadastrado.');}
redirecionar('index.php');
