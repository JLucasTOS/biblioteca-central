<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar('index.php');
validar_csrf();
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare("UPDATE livro SET ativo = IF(ativo=1,0,1) WHERE id_livro=?");
$st->execute([$id]);
mensagem('sucesso', 'Status do livro atualizado.');
redirecionar('index.php');
