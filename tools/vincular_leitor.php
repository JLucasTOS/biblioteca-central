<?php
// Cria login para leitor já cadastrado, mediante verificação administrativa fora do sistema.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Somente CLI.'); }
require_once __DIR__ . '/../config/conexao.php';
if ($argc < 3) { fwrite(STDERR, "Uso: php tools/vincular_leitor.php email senha\n"); exit(1); }
$email = strtolower(trim($argv[1])); $senha = $argv[2];
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 10) { fwrite(STDERR, "E-mail inválido ou senha curta.\n"); exit(1); }
try {
    $pdo->beginTransaction();
    $st = $pdo->prepare('SELECT id_usuario,ativo FROM usuario WHERE email=? FOR UPDATE'); $st->execute([$email]);
    $usuario = $st->fetch();
    if (!$usuario || !(int)$usuario['ativo']) throw new RuntimeException('Leitor não existe ou está inativo.');
    $st = $pdo->prepare('SELECT id_conta FROM conta WHERE email=? OR id_usuario=?');
    $st->execute([$email, $usuario['id_usuario']]);
    if ($st->fetch()) throw new RuntimeException('Já existe uma conta vinculada.');
    $st = $pdo->prepare("INSERT INTO conta (email,senha_hash,perfil,id_usuario) VALUES (?,?,'leitor',?)");
    $st->execute([$email, password_hash($senha, PASSWORD_DEFAULT), $usuario['id_usuario']]);
    $pdo->commit(); echo "Conta de leitor vinculada.\n";
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
