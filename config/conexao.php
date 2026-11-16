<?php
declare(strict_types=1);

/*
 * Conexão local com o MariaDB do XAMPP.
 * Se você configurou senha para o usuário root, informe em $senha.
 */
$host = '127.0.0.1';
$banco = 'biblioteca_central';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco. Confira se o MySQL/MariaDB está iniciado no XAMPP e se o banco biblioteca_central existe. Detalhe: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
