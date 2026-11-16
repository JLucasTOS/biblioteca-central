<?php
// Execute SOMENTE pelo terminal (CLI): php tools/criar_admin.php email senha [id_funcionario]
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Disponível apenas no terminal.');}
require_once __DIR__.'/../config/conexao.php';
if($argc<3){fwrite(STDERR,"Uso: php tools/criar_admin.php email senha [id_funcionario]\n");exit(1);}
$email=strtolower(trim($argv[1]));$senha=$argv[2];$funcionario=isset($argv[3])?(int)$argv[3]:null;
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($senha)<12){fwrite(STDERR,"E-mail inválido ou senha com menos de 12 caracteres.\n");exit(1);}
try{
 $st=$pdo->prepare('SELECT id_conta FROM conta WHERE email=?');$st->execute([$email]);if($st->fetch())throw new RuntimeException('Este e-mail já possui conta.');
 if($funcionario!==null){$st=$pdo->prepare('SELECT id_funcionario FROM funcionario WHERE id_funcionario=? AND ativo=1');$st->execute([$funcionario]);if(!$st->fetch())throw new RuntimeException('Funcionário inexistente ou inativo.');}
 $st=$pdo->prepare("INSERT INTO conta(email,senha_hash,perfil,id_funcionario) VALUES(?,?,'admin',?)");$st->execute([$email,password_hash($senha,PASSWORD_DEFAULT),$funcionario]);
 echo "Administrador criado com sucesso.\n";
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
