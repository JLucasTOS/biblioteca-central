<?php
require_once __DIR__.'/../config/conexao.php'; require_once __DIR__.'/../includes/auth.php'; exigir_conta_ativa($pdo,'admin');
$usuarios=$pdo->query("SELECT id_usuario,nome,email FROM usuario WHERE ativo=1 ORDER BY nome")->fetchAll();
$livros=$pdo->query("SELECT id_livro,titulo,disponivel FROM vw_livro_disponivel WHERE ativo=1 AND disponivel>0 ORDER BY titulo")->fetchAll();
$funcionarios=$pdo->query("SELECT id_funcionario,nome,cargo FROM funcionario WHERE ativo=1 ORDER BY nome")->fetchAll();
cabecalho('Novo empréstimo','emprestimos');
?>
<div class="section-heading"><div><span class="eyebrow">CIRCULAÇÃO</span><h2>Registrar empréstimo</h2><p class="muted">O prazo padrão definido no banco é de 14 dias.</p></div><a class="button button-ghost" href="index.php">← Voltar</a></div>
<div class="panel form-panel"><form method="post" action="salvar.php" class="form-grid">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label class="field field-wide">Leitor *<select name="id_usuario" required><option value="">Selecione o leitor</option><?php foreach($usuarios as $u): ?><option value="<?= $u['id_usuario'] ?>"><?= e($u['nome'].' — '.$u['email']) ?></option><?php endforeach; ?></select></label>
<label class="field field-wide">Livro disponível *<select name="id_livro" required><option value="">Selecione o livro</option><?php foreach($livros as $l): ?><option value="<?= $l['id_livro'] ?>"><?= e($l['titulo'].' — '.$l['disponivel'].' disponível(is)') ?></option><?php endforeach; ?></select><?php if(!$livros): ?><small class="muted">Não há livros disponíveis no momento.</small><?php endif; ?></label>
<label class="field field-wide">Funcionário responsável<select name="id_funcionario"><option value="">Não informado</option><?php foreach($funcionarios as $f): ?><option value="<?= $f['id_funcionario'] ?>"><?= e($f['nome'].' — '.$f['cargo']) ?></option><?php endforeach; ?></select></label>
<div class="notice field-wide">Ao confirmar, o sistema chama a procedure <code>emprestar_livro</code> do MariaDB. Ela verifica o estoque e registra a data prevista para 14 dias depois.</div>
<div class="form-actions field-wide"><button class="button button-primary" <?= (!$usuarios||!$livros)?'disabled':'' ?>>Confirmar empréstimo</button><a class="button button-ghost" href="index.php">Cancelar</a></div>
</form></div>
<?php rodape(); ?>
