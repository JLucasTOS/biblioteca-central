<?php
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');

require __DIR__ . '/formulario.php';
