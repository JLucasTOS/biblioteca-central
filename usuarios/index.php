<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT u.*, COUNT(e.id_emprestimo) AS emprestimos FROM usuario u LEFT JOIN emprestimo e ON e.id_usuario=u.id_usuario WHERE (:q='' OR u.nome LIKE :termo1 OR u.email LIKE :termo2) GROUP BY u.id_usuario ORDER BY u.nome");
$termo="%{$q}%"; $st->execute(['q'=>$q,'termo1'=>$termo,'termo2'=>$termo]);
$usuarios = $st->fetchAll();
cabecalho('Leitores', 'usuarios');
?>
<div class="section-heading"><div><span class="eyebrow">CADASTROS</span><h2>Leitores da biblioteca</h2><p class="muted">Consulte e mantenha os dados dos usuários.</p></div><a class="button button-primary" href="cadastrar.php">＋ Novo leitor</a></div>
<form class="search-bar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Buscar por nome ou e-mail..."><button class="button button-dark">Pesquisar</button></form>
<div class="panel table-wrap"><?php if(!$usuarios): ?><div class="empty-state">Nenhum leitor encontrado.</div><?php else: ?>
<table><thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Empréstimos</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php foreach($usuarios as $u): ?><tr><td class="strong"><?= e($u['nome']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['telefone']) ?></td><td><?= (int)$u['emprestimos'] ?></td><td><span class="status <?= $u['ativo']?'status-ok':'status-off' ?>"><?= $u['ativo']?'Ativo':'Inativo' ?></span></td><td class="actions"><a href="editar.php?id=<?= (int)$u['id_usuario'] ?>">Editar</a><form method="post" action="status.php" onsubmit="return confirm('Alterar status deste leitor?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>"><button class="link-button"><?= $u['ativo']?'Desativar':'Ativar' ?></button></form></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></div>
<?php rodape(); ?>
