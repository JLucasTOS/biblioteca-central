<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(mixed $valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $destino): never {
    header("Location: {$destino}");
    exit;
}

function mensagem(string $tipo, string $texto): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function mostrar_mensagem(): void {
    if (!empty($_SESSION['flash'])) {
        $m = $_SESSION['flash'];
        $classe = $m['tipo'] === 'sucesso' ? 'alert-success' : 'alert-error';
        echo '<div class="alert ' . $classe . '">' . e($m['texto']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validar_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
}

function cabecalho(string $titulo, string $pagina = ''): void {
    $base='/biblioteca_central_sistema';
    $auth=$_SESSION['auth']??null;
    $admin=$auth && $auth['perfil']==='admin';
    $itens=$admin ? [
        'inicio'=>['Painel',$base.'/index.php'],
        'livros'=>['Livros',$base.'/livros/index.php'],
        'usuarios'=>['Leitores',$base.'/usuarios/index.php'],
        'emprestimos'=>['Empréstimos',$base.'/emprestimos/index.php'],
        'pedidos'=>['Solicitações',$base.'/admin/pedidos.php'],
        'devolucoes'=>['Devoluções solicitadas',$base.'/admin/devolucoes.php']
    ] : [
        'catalogo'=>['Catálogo',$base.'/leitor/index.php'],
        'meus'=>['Meus pedidos',$base.'/leitor/pedidos.php']
    ];
    echo '<script>
    (function () {
        var tema = "light";

        try {
            tema = localStorage.getItem("biblioteca-tema") || "light";
        } catch (erro) {
            tema = "light";
        }

        document.documentElement.setAttribute(
            "data-theme",
            tema === "dark" ? "dark" : "light"
        );
    })();
</script>';
    echo '<title>'.e($titulo).' | Biblioteca Central</title><link rel="stylesheet" href="'.$base.'/assets/css/style.css"></head><body>';
    echo '<aside class="sidebar"><a class="brand" href="'.$base.'/index.php"><span class="brand-mark">B</span><span>Biblioteca<br><small>Central</small></span></a><nav>';
    foreach($itens as $chave=>[$nome,$url]) echo '<a class="nav-link'.($pagina===$chave?' active':'').'" href="'.$url.'">'.e($nome).'</a>';
    echo '<a class="nav-link" href="'.$base.'/logout.php">Sair da conta</a></nav><div class="sidebar-foot">'.($admin?'Área administrativa':'Portal do leitor').'<br>Ambiente local XAMPP</div></aside>';
    echo '<main class="main">
    <header class="topbar">
        <div>
            <span class="eyebrow">BIBLIOTECA CENTRAL</span>
            <h1>'.e($titulo).'</h1>
        </div>

        <div class="topbar-actions">
            <button
                type="button"
                id="alternar-tema"
                class="theme-toggle"
                aria-label="Ativar modo escuro"
                aria-pressed="false"
            >
                <span class="theme-icon" aria-hidden="true">☾</span>
                <span class="theme-label">Modo escuro</span>
            </button>

            <span class="local-pill">'
                .($admin?'Administração':'Área do leitor').
            '</span>
        </div>
    </header>

    <section class="content">';
    mostrar_mensagem();
}
function rodape(): void {
    echo '</section><footer class="footer">Biblioteca Central · Projeto acadêmico</footer></main>';
    echo '<script src="/biblioteca_central_sistema/assets/js/app.js"></script></body></html>';
}
