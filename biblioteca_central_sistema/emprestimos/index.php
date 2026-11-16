<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
$filtro=$_GET['filtro']??'abertos';
$where=$filtro==='todos'?'':($filtro==='atrasados'?'WHERE e.data_devolucao IS NULL AND e.data_prevista<CURRENT_DATE':'WHERE e.data_devolucao IS NULL');
$sql="SELECT e.id_emprestimo,e.data_emprestimo,e.data_prevista,e.data_devolucao,u.nome AS usuario,l.titulo,f.nome AS funcionario FROM emprestimo e JOIN usuario u ON u.id_usuario=e.id_usuario JOIN item_emprestimo i ON i.id_emprestimo=e.id_emprestimo JOIN livro l ON l.id_livro=i.id_livro LEFT JOIN funcionario f ON f.id_funcionario=e.id_funcionario $where ORDER BY e.id_emprestimo DESC";
$emprestimos=$pdo->query($sql)->fetchAll();
cabecalho('Empréstimos','emprestimos');
?>
<div class="section-heading"><div><span class="eyebrow">CIRCULAÇÃO</span><h2>Empréstimos e devoluções</h2><p class="muted">Acompanhe os livros que estão com os leitores.</p></div><a class="button button-primary" href="realizar.php">＋ Novo empréstimo</a></div>
<div class="tabs"><a class="<?= $filtro==='abertos'?'selected':'' ?>" href="?filtro=abertos">Em aberto</a><a class="<?= $filtro==='atrasados'?'selected':'' ?>" href="?filtro=atrasados">Atrasados</a><a class="<?= $filtro==='todos'?'selected':'' ?>" href="?filtro=todos">Todos</a></div>
<div class="panel table-wrap"><?php if(!$emprestimos): ?><div class="empty-state">Nenhum empréstimo nesta categoria.</div><?php else: ?>
<table><thead><tr><th>Leitor</th><th>Livro</th><th>Retirada</th><th>Prevista</th><th>Atendente</th><th>Status / ação</th></tr></thead><tbody>
<?php foreach($emprestimos as $em): $atrasado=!$em['data_devolucao'] && $em['data_prevista']<date('Y-m-d'); ?>
<tr><td class="strong"><?= e($em['usuario']) ?></td><td><?= e($em['titulo']) ?></td><td><?= e(date('d/m/Y',strtotime($em['data_emprestimo']))) ?></td><td><?= e(date('d/m/Y',strtotime($em['data_prevista']))) ?></td><td><?= e($em['funcionario']?:'—') ?></td><td><?php if($em['data_devolucao']): ?><span class="status status-ok">Devolvido em <?= e(date('d/m/Y',strtotime($em['data_devolucao']))) ?></span><?php elseif($atrasado): ?><span class="status status-late">Atrasado</span><?php else: ?><span class="status status-open">Em aberto</span><?php endif; ?>
<?php if(!$em['data_devolucao']): ?><form method="post" action="devolver.php" class="inline-form" onsubmit="return confirm('Confirmar a devolução deste livro?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$em['id_emprestimo'] ?>"><button class="button button-small button-dark">Registrar devolução</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table><?php endif; ?></div>
<?php rodape(); ?>
