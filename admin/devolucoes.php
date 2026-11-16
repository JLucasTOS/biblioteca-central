<?php
require_once __DIR__.'/../config/conexao.php';require_once __DIR__.'/../includes/auth.php';exigir_conta_ativa($pdo,'admin');
$pedidos=$pdo->query("SELECT d.*,u.nome AS leitor,l.titulo,e.data_devolucao FROM pedido_devolucao d JOIN usuario u ON u.id_usuario=d.id_usuario JOIN emprestimo e ON e.id_emprestimo=d.id_emprestimo JOIN item_emprestimo i ON i.id_emprestimo=e.id_emprestimo JOIN livro l ON l.id_livro=i.id_livro ORDER BY FIELD(d.status,'pendente','recusado','confirmado'),d.id_pedido_devolucao DESC")->fetchAll();
cabecalho('Devoluções solicitadas','devolucoes');
?>
<div class="section-heading"><div><span class="eyebrow">CONFERÊNCIA</span><h2>Pedidos de devolução</h2><p class="muted">Confirme somente após receber o exemplar.</p></div></div>
<div class="panel table-wrap"><?php if(!$pedidos): ?><div class="empty-state">Nenhum pedido.</div><?php else: ?><table><thead><tr><th>Leitor</th><th>Livro</th><th>Solicitado em</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php foreach($pedidos as $d): ?><tr><td class="strong"><?= e($d['leitor']) ?></td><td><?= e($d['titulo']) ?></td><td><?= e(date('d/m/Y H:i',strtotime($d['solicitado_em']))) ?></td><td><?= e(ucfirst($d['status'])) ?></td><td><?php if($d['status']==='pendente' && !$d['data_devolucao']): ?>
<form method="post" action="decidir_devolucao.php" class="decision-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$d['id_pedido_devolucao'] ?>"><input name="observacao" maxlength="500" placeholder="Observação"><button class="button button-small button-primary" name="decisao" value="confirmar" onclick="return confirm('O livro já foi entregue?')">Confirmar recebimento</button><button class="button button-small button-ghost" name="decisao" value="recusar">Recusar</button></form>
<?php else: ?><?= e($d['observacao']?:'—') ?><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div><?php rodape(); ?>
