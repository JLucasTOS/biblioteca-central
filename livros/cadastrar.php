<?php
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');

$_GET['id'] = 0;
require __DIR__ . '/formulario.php';
