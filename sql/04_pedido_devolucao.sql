USE biblioteca_central;
CREATE TABLE IF NOT EXISTS pedido_devolucao (
 id_pedido_devolucao INT AUTO_INCREMENT PRIMARY KEY,
 id_emprestimo INT NOT NULL,
 id_usuario INT NOT NULL,
 status ENUM('pendente','confirmado','recusado') NOT NULL DEFAULT 'pendente',
 solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 analisado_em DATETIME NULL,
 observacao VARCHAR(500) NULL,
 id_conta_admin INT NULL,
 FOREIGN KEY (id_emprestimo) REFERENCES emprestimo(id_emprestimo),
 FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
 FOREIGN KEY (id_conta_admin) REFERENCES conta(id_conta),
 INDEX idx_devolucao_status (status,solicitado_em),
 INDEX idx_devolucao_emprestimo (id_emprestimo,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
