<?php
require_once __DIR__.'/includes/funcoes.php';
if($_SERVER['REQUEST_METHOD']!=='POST' && $_SERVER['REQUEST_METHOD']!=='GET'){http_response_code(405);exit;}
$_SESSION=[];session_regenerate_id(true);mensagem('sucesso','Você saiu da sua conta.');redirecionar('login.php');
