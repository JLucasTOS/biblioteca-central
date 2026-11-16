<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario = ['nome'=>'','email'=>'','telefone'=>'','data_nascimento'=>''];
if ($id) {
    $st=$pdo->prepare("SELECT * FROM usuario WHERE id_usuario=?"); $st->execute([$id]); $usuario=$st->fetch();
    if(!$usuario){mensagem('erro','Leitor não encontrado.');redirecionar('index.php');}
}
cabecalho($id?'Editar leitor':'Cadastrar leitor','usuarios');
?>
<div class="section-heading"><div><span class="eyebrow">CADASTROS</span><h2><?= $id?'Editar leitor':'Novo leitor' ?></h2></div><a class="button button-ghost" href="index.php">← Voltar</a></div>
<div class="panel form-panel"><form method="post" action="salvar.php" class="form-grid">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
<label class="field field-wide">Nome completo *<input name="nome" required maxlength="150" value="<?= e($usuario['nome']) ?>"></label>
<label class="field field-wide">E-mail *<input type="email" name="email" required maxlength="255" value="<?= e($usuario['email']) ?>"></label>
<label class="field">Telefone<input name="telefone" maxlength="30" value="<?= e($usuario['telefone']) ?>"></label>
<label class="field">Data de nascimento<input type="date" name="data_nascimento" value="<?= e($usuario['data_nascimento']) ?>"></label>
<div class="form-actions field-wide"><button class="button button-primary">Salvar leitor</button><a class="button button-ghost" href="index.php">Cancelar</a></div>
</form></div>
<?php rodape(); ?>
