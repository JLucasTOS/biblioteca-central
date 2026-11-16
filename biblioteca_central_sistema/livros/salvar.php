<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
exigir_conta_ativa($pdo,'admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar('index.php');
validar_csrf();

$id = (int)($_POST['id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$ano = ($_POST['ano_publicacao'] ?? '') !== '' ? (int)$_POST['ano_publicacao'] : null;
$estoque = filter_var($_POST['estoque'] ?? null, FILTER_VALIDATE_INT);
$preco = filter_var($_POST['preco_reposicao'] ?? null, FILTER_VALIDATE_FLOAT);
$idioma = trim($_POST['idioma'] ?? 'Português') ?: 'Português';
$idEditora = ($_POST['id_editora'] ?? '') !== '' ? (int)$_POST['id_editora'] : null;
$idCategoria = ($_POST['id_categoria'] ?? '') !== '' ? (int)$_POST['id_categoria'] : null;
$idAutor = ($_POST['id_autor'] ?? '') !== '' ? (int)$_POST['id_autor'] : null;

if ($titulo === '' || $estoque === false || $estoque < 0 || $preco === false || $preco < 0 || ($ano !== null && ($ano < 1 || $ano > 2100))) {
    mensagem('erro', 'Confira os campos obrigatórios e os valores informados.');
    redirecionar($id ? "editar.php?id={$id}" : 'cadastrar.php');
}
try {
    $pdo->beginTransaction();
    if ($id) {
        $st = $pdo->prepare("UPDATE livro SET titulo=?, isbn=?, ano_publicacao=?, estoque=?, preco_reposicao=?, idioma=?, id_editora=?, id_categoria=? WHERE id_livro=?");
        $st->execute([$titulo, $isbn ?: null, $ano, $estoque, $preco, $idioma, $idEditora, $idCategoria, $id]);
        $pdo->prepare("DELETE FROM livro_autor WHERE id_livro=?")->execute([$id]);
        $idLivro = $id;
    } else {
        $st = $pdo->prepare("INSERT INTO livro (titulo,isbn,ano_publicacao,estoque,preco_reposicao,idioma,id_editora,id_categoria) VALUES (?,?,?,?,?,?,?,?)");
        $st->execute([$titulo, $isbn ?: null, $ano, $estoque, $preco, $idioma, $idEditora, $idCategoria]);
        $idLivro = (int)$pdo->lastInsertId();
    }
    if ($idAutor) {
        $pdo->prepare("INSERT INTO livro_autor (id_livro,id_autor) VALUES (?,?)")->execute([$idLivro, $idAutor]);
    }
    $pdo->commit();
    mensagem('sucesso', $id ? 'Livro atualizado com sucesso.' : 'Livro cadastrado com sucesso.');
    redirecionar('index.php');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    mensagem('erro', str_contains($e->getMessage(), 'Duplicate') ? 'Já existe um livro com esse título ou ISBN.' : 'Não foi possível salvar. Verifique se os dados relacionados existem.');
    redirecionar($id ? "editar.php?id={$id}" : 'cadastrar.php');
}
