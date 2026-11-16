<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'leitor');
$q=trim($_GET['q']??'');
$st=$pdo->prepare("SELECT l.id_livro,l.titulo,l.isbn,l.ano_publicacao,l.estoque,v.disponivel,c.nome AS categoria,GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') AS autores FROM livro l JOIN vw_livro_disponivel v ON v.id_livro=l.id_livro LEFT JOIN categoria c ON c.id_categoria=l.id_categoria LEFT JOIN livro_autor la ON la.id_livro=l.id_livro LEFT JOIN autor a ON a.id_autor=la.id_autor WHERE l.ativo=1 AND (l.titulo LIKE :termo1 OR :q='' OR l.isbn LIKE :termo2) GROUP BY l.id_livro ORDER BY l.titulo");
$st->execute(['termo1'=>'%'.$q.'%','termo2'=>'%'.$q.'%','q'=>$q]);$livros=$st->fetchAll();
$st=$pdo->prepare("SELECT id_livro FROM pedido_emprestimo WHERE id_usuario=? AND status IN ('pendente','processando')");$st->execute([usuario_logado()['id_usuario']]);$pendentes=array_fill_keys($st->fetchAll(PDO::FETCH_COLUMN),true);
cabecalho('Catálogo de livros','catalogo');
?>
<div class="welcome"><div><span class="eyebrow light">PORTAL DO LEITOR</span><h2>Encontre sua próxima leitura.</h2><p>Pesquise os títulos e solicite empréstimos para aprovação da biblioteca.</p></div><div class="welcome-icon">▤</div></div>
<div class="section-heading"><div><span class="eyebrow">ACERVO DISPONÍVEL</span><h2>Explore os livros</h2></div><a class="button button-ghost" href="pedidos.php">Acompanhar meus pedidos</a></div>
<form method="get" class="search-bar"><input name="q" value="<?= e($q) ?>" placeholder="Buscar título ou ISBN"><button class="button button-dark">Pesquisar</button></form>
<div class="book-grid">
<?php foreach($livros as $l): ?><article class="book-card"><div class="book-icon">▤</div><span class="eyebrow"><?= e($l['categoria']?:'LITERATURA') ?></span><h3><?= e($l['titulo']) ?></h3><p class="muted"><?= e($l['autores']?:'Autor não informado') ?></p><div class="book-meta"><span class="status <?= $l['disponivel']>0?'status-ok':'status-late' ?>"><?= $l['disponivel']>0?(int)$l['disponivel'].' disponível(is)':'Indisponível' ?></span><small><?= e($l['ano_publicacao']) ?></small></div>
<form method="post" action="solicitar.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id_livro" value="<?= (int)$l['id_livro'] ?>"><button class="button button-primary" <?= ($l['disponivel']<=0 || isset($pendentes[$l['id_livro']]))?'disabled':'' ?>><?= isset($pendentes[$l['id_livro']])?'Pedido pendente':'Solicitar empréstimo' ?></button></form></article><?php endforeach; ?>
<?php if(!$livros): ?><div class="empty-state">Nenhum livro encontrado.</div><?php endif; ?>
</div><?php rodape(); ?>
