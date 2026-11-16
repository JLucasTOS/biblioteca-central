-- Execute UMA VEZ após o script original biblioteca_central_mariadb_xampp.sql.
-- Não apaga nem substitui livros, leitores ou empréstimos existentes.
USE biblioteca_central;

CREATE TABLE IF NOT EXISTS conta (
 id_conta INT AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(255) NOT NULL UNIQUE,
 senha_hash VARCHAR(255) NOT NULL,
 perfil ENUM('admin','leitor') NOT NULL,
 id_usuario INT NULL UNIQUE,
 id_funcionario INT NULL UNIQUE,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_conta_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
 CONSTRAINT fk_conta_funcionario FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_emprestimo (
 id_pedido INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL,
 id_livro INT NOT NULL,
 status ENUM('pendente','processando','aprovado','recusado','cancelado') NOT NULL DEFAULT 'pendente',
 solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 analisado_em DATETIME NULL,
 id_funcionario INT NULL,
 id_emprestimo INT NULL UNIQUE,
 observacao VARCHAR(500) NULL,
 CONSTRAINT fk_pedido_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
 CONSTRAINT fk_pedido_livro FOREIGN KEY (id_livro) REFERENCES livro(id_livro),
 CONSTRAINT fk_pedido_funcionario FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario),
 CONSTRAINT fk_pedido_emprestimo FOREIGN KEY (id_emprestimo) REFERENCES emprestimo(id_emprestimo),
 INDEX idx_pedido_status (status, solicitado_em),
 INDEX idx_pedido_usuario (id_usuario, status)
) ENGINE=InnoDB;
