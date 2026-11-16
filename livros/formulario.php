<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$livro = ['titulo'=>'','isbn'=>'','ano_publicacao'=>'','estoque'=>1,'preco_reposicao'=>'50.00','idioma'=>'Português','id_editora'=>'','id_categoria'=>'','id_autor'=>''];
if ($id) {
    $st = $pdo->prepare("SELECT * FROM livro WHERE id_livro = ?");
    $st->execute([$id]);
    $livro = $st->fetch();
    if (!$livro) { mensagem('erro', 'Livro não encontrado.'); redirecionar('index.php'); }
    $st = $pdo->prepare("SELECT id_autor FROM livro_autor WHERE id_livro = ? LIMIT 1");
    $st->execute([$id]);
    $livro['id_autor'] = $st->fetchColumn() ?: '';
}
$editoras = $pdo->query("SELECT id_editora, nome FROM editora ORDER BY nome")->fetchAll();
$categorias = $pdo->query("SELECT id_categoria, nome FROM categoria ORDER BY nome")->fetchAll();
$autores = $pdo->query("SELECT id_autor, nome FROM autor ORDER BY nome")->fetchAll();

cabecalho($id ? 'Editar livro' : 'Cadastrar livro', 'livros');
?>
<div class="section-heading"><div><span class="eyebrow">CATÁLOGO</span><h2><?= $id ? 'Editar informações' : 'Adicionar ao acervo' ?></h2><p class="muted">Preencha os dados principais do título.</p></div><a class="button button-ghost" href="index.php">← Voltar</a></div>
<div class="panel form-panel">
<form method="post" action="salvar.php" class="form-grid">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
<label class="field field-wide">Título do livro *<input name="titulo" required maxlength="200" value="<?= e($livro['titulo']) ?>"></label>
<label class="field">ISBN<input name="isbn" maxlength="20" value="<?= e($livro['isbn']) ?>" placeholder="Opcional"></label>
<label class="field">Ano de publicação<input name="ano_publicacao" type="number" min="1" max="2100" value="<?= e($livro['ano_publicacao']) ?>"></label>
<label class="field">Estoque *<input name="estoque" type="number" min="0" required value="<?= e($livro['estoque']) ?>"></label>
<label class="field">Preço de reposição (R$)<input name="preco_reposicao" type="number" min="0" step="0.01" required value="<?= e($livro['preco_reposicao']) ?>"></label>
<label class="field">Idioma<input name="idioma" maxlength="60" value="<?= e($livro['idioma']) ?>"></label>
<label class="field">Categoria<select name="id_categoria"><option value="">Não informada</option><?php foreach($categorias as $c): ?><option value="<?= $c['id_categoria'] ?>" <?= (string)$livro['id_categoria']===(string)$c['id_categoria']?'selected':'' ?>><?= e($c['nome']) ?></option><?php endforeach; ?></select></label>
<label class="field">Editora<select name="id_editora"><option value="">Não informada</option><?php foreach($editoras as $ed): ?><option value="<?= $ed['id_editora'] ?>" <?= (string)$livro['id_editora']===(string)$ed['id_editora']?'selected':'' ?>><?= e($ed['nome']) ?></option><?php endforeach; ?></select></label>
<label class="field field-wide">Autor principal<select name="id_autor"><option value="">Não informado</option><?php foreach($autores as $a): ?><option value="<?= $a['id_autor'] ?>" <?= (string)$livro['id_autor']===(string)$a['id_autor']?'selected':'' ?>><?= e($a['nome']) ?></option><?php endforeach; ?></select><small class="muted">Nesta primeira versão, o formulário associa um autor principal. O banco continua permitindo vários autores por livro.</small></label>
<div class="form-actions field-wide"><button class="button button-primary">Salvar livro</button><a class="button button-ghost" href="index.php">Cancelar</a></div>
</form></div>
<?php rodape(); ?>
