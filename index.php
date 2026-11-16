<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/includes/auth.php';
if (!usuario_logado()) redirecionar('login.php');
if (usuario_logado()['perfil'] === 'leitor') redirecionar('leitor/index.php');
exigir_conta_ativa($pdo, 'admin');

$totais = [
    'livros' => (int)$pdo->query("SELECT COUNT(*) FROM livro WHERE ativo = 1")->fetchColumn(),
    'leitores' => (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE ativo = 1")->fetchColumn(),
    'abertos' => (int)$pdo->query("SELECT COUNT(*) FROM emprestimo WHERE data_devolucao IS NULL")->fetchColumn(),
    'atrasados' => (int)$pdo->query("SELECT COUNT(*) FROM emprestimo WHERE data_devolucao IS NULL AND data_prevista < CURRENT_DATE")->fetchColumn(),
];
$recentes = $pdo->query("
    SELECT e.id_emprestimo, u.nome AS usuario, l.titulo, e.data_emprestimo, e.data_prevista
    FROM emprestimo e
    JOIN usuario u ON u.id_usuario = e.id_usuario
    JOIN item_emprestimo i ON i.id_emprestimo = e.id_emprestimo
    JOIN livro l ON l.id_livro = i.id_livro
    WHERE e.data_devolucao IS NULL
    ORDER BY e.id_emprestimo DESC LIMIT 6
")->fetchAll();

cabecalho('Painel principal', 'inicio');
?>
<div class="welcome">
  <div><span class="eyebrow light">BEM-VINDO AO SISTEMA</span><h2>Uma biblioteca organizada começa aqui.</h2><p>Gerencie o acervo, os leitores e a circulação dos livros em um só lugar.</p></div>
  <div class="welcome-icon">▤</div>
</div>
<div class="stats-grid">
  <article class="stat-card"><span class="stat-label">Livros ativos</span><strong><?= $totais['livros'] ?></strong><span class="stat-note">Títulos no catálogo</span></article>
  <article class="stat-card"><span class="stat-label">Leitores ativos</span><strong><?= $totais['leitores'] ?></strong><span class="stat-note">Pessoas cadastradas</span></article>
  <article class="stat-card"><span class="stat-label">Empréstimos em aberto</span><strong><?= $totais['abertos'] ?></strong><span class="stat-note">Aguardando devolução</span></article>
  <article class="stat-card warning"><span class="stat-label">Em atraso</span><strong><?= $totais['atrasados'] ?></strong><span class="stat-note">Precisam de acompanhamento</span></article>
</div>
<div class="section-heading"><div><span class="eyebrow">ACOMPANHAMENTO</span><h2>Empréstimos em aberto</h2></div><a class="button button-primary" href="emprestimos/index.php">Ver empréstimos</a></div>
<div class="panel table-wrap">
  <?php if (!$recentes): ?><div class="empty-state">Ainda não existem empréstimos em aberto.</div>
  <?php else: ?>
  <table><thead><tr><th>Leitor</th><th>Livro</th><th>Retirada</th><th>Devolução prevista</th></tr></thead><tbody>
  <?php foreach ($recentes as $r): ?>
    <tr><td><?= e($r['usuario']) ?></td><td class="strong"><?= e($r['titulo']) ?></td><td><?= e(date('d/m/Y', strtotime($r['data_emprestimo']))) ?></td><td><?= e(date('d/m/Y', strtotime($r['data_prevista']))) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table><?php endif; ?>
</div>
<div class="quick-grid">
  <a class="quick-card" href="livros/cadastrar.php"><span>＋</span><div><b>Cadastrar livro</b><small>Adicionar um título ao acervo</small></div></a>
  <a class="quick-card" href="usuarios/cadastrar.php"><span>＋</span><div><b>Cadastrar leitor</b><small>Registrar um novo usuário</small></div></a>
  <a class="quick-card" href="emprestimos/realizar.php"><span>↗</span><div><b>Novo empréstimo</b><small>Registrar a retirada de um livro</small></div></a>
</div>
<?php rodape(); ?>
