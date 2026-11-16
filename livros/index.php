<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');

$busca = trim($_GET['q'] ?? '');
$sql = "SELECT l.id_livro, l.titulo, l.isbn, l.ano_publicacao, l.estoque, l.ativo,
               c.nome AS categoria, ed.nome AS editora,
               GROUP_CONCAT(DISTINCT a.nome ORDER BY a.nome SEPARATOR ', ') AS autores,
               COALESCE(v.disponivel, l.estoque) AS disponivel
        FROM livro l
        LEFT JOIN categoria c ON c.id_categoria = l.id_categoria
        LEFT JOIN editora ed ON ed.id_editora = l.id_editora
        LEFT JOIN livro_autor la ON la.id_livro = l.id_livro
        LEFT JOIN autor a ON a.id_autor = la.id_autor
        LEFT JOIN vw_livro_disponivel v ON v.id_livro = l.id_livro
        WHERE (:busca = '' OR l.titulo LIKE :termo1 OR l.isbn LIKE :termo2 OR a.nome LIKE :termo3)
        GROUP BY l.id_livro
        ORDER BY l.titulo";
$stmt = $pdo->prepare($sql);
$termo = "%{$busca}%";
$stmt->execute(['busca' => $busca, 'termo1' => $termo, 'termo2' => $termo, 'termo3' => $termo]);
$livros = $stmt->fetchAll();

cabecalho('Acervo de livros', 'livros');
?>
<div class="section-heading"><div><span class="eyebrow">CATÁLOGO</span><h2>Livros cadastrados</h2><p class="muted">Pesquise, cadastre e atualize os títulos da biblioteca.</p></div><a class="button button-primary" href="cadastrar.php">＋ Novo livro</a></div>
<form class="search-bar" method="get"><input name="q" value="<?= e($busca) ?>" placeholder="Buscar por título, ISBN ou autor..."><button class="button button-dark">Pesquisar</button><?php if ($busca): ?><a class="button button-ghost" href="index.php">Limpar</a><?php endif; ?></form>
<div class="panel table-wrap">
<?php if (!$livros): ?><div class="empty-state">Nenhum livro encontrado.</div><?php else: ?>
<table><thead><tr><th>Livro</th><th>Categoria</th><th>Editora</th><th>Estoque</th><th>Disponíveis</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($livros as $l): ?>
<tr>
<td><div class="strong"><?= e($l['titulo']) ?></div><small class="muted"><?= e($l['autores'] ?: 'Autor não informado') ?></small></td>
<td><?= e($l['categoria'] ?: '—') ?></td><td><?= e($l['editora'] ?: '—') ?></td>
<td><?= (int)$l['estoque'] ?></td><td><span class="number-pill"><?= (int)$l['disponivel'] ?></span></td>
<td><span class="status <?= $l['ativo'] ? 'status-ok' : 'status-off' ?>"><?= $l['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
<td class="actions"><a href="editar.php?id=<?= (int)$l['id_livro'] ?>">Editar</a><form method="post" action="status.php" onsubmit="return confirm('Alterar o status deste livro?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$l['id_livro'] ?>"><button class="link-button"><?= $l['ativo'] ? 'Desativar' : 'Ativar' ?></button></form></td>
</tr>
<?php endforeach; ?></tbody></table><?php endif; ?>
</div>
<?php rodape(); ?>
