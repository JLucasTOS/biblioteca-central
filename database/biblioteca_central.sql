-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/11/2026 às 21:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `biblioteca_central`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `devolver_emprestimo` (IN `p_id_emprestimo` INT)   BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM emprestimo
    WHERE id_emprestimo = p_id_emprestimo AND data_devolucao IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Empréstimo não encontrado ou já devolvido.';
  END IF;
  UPDATE emprestimo
  SET data_devolucao = CURRENT_DATE
  WHERE id_emprestimo = p_id_emprestimo AND data_devolucao IS NULL;
  SELECT 'Devolução registrada com sucesso.' AS mensagem;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `emprestar_livro` (IN `p_id_usuario` INT, IN `p_id_livro` INT, IN `p_id_funcionario` INT)   BEGIN
  DECLARE v_disponivel INT DEFAULT 0;
  DECLARE v_estoque INT DEFAULT 0;
  DECLARE v_emprestimo INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;
  IF NOT EXISTS (SELECT 1 FROM usuario WHERE id_usuario = p_id_usuario AND ativo = TRUE) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuário não encontrado ou inativo.';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM livro WHERE id_livro = p_id_livro AND ativo = TRUE) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Livro não encontrado ou inativo.';
  END IF;

  SELECT estoque INTO v_estoque
  FROM livro WHERE id_livro = p_id_livro FOR UPDATE;
  SELECT v_estoque - COUNT(*) INTO v_disponivel
  FROM item_emprestimo i
  JOIN emprestimo e ON e.id_emprestimo = i.id_emprestimo
  WHERE i.id_livro = p_id_livro AND e.data_devolucao IS NULL;
  IF v_disponivel <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Livro indisponível.';
  END IF;

  IF EXISTS (
    SELECT 1 FROM item_emprestimo i
    JOIN emprestimo e ON e.id_emprestimo = i.id_emprestimo
    WHERE i.id_livro = p_id_livro AND e.id_usuario = p_id_usuario
      AND e.data_devolucao IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Este usuário já está com este livro.';
  END IF;

  INSERT INTO emprestimo (data_prevista, id_usuario, id_funcionario)
  VALUES (DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY), p_id_usuario, p_id_funcionario);
  SET v_emprestimo = LAST_INSERT_ID();
  INSERT INTO item_emprestimo (id_emprestimo, id_livro) VALUES (v_emprestimo, p_id_livro);
  COMMIT;
  SELECT v_emprestimo AS id_emprestimo_criado;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `autor`
--

CREATE TABLE `autor` (
  `id_autor` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nacionalidade` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `autor`
--

INSERT INTO `autor` (`id_autor`, `nome`, `nacionalidade`) VALUES
(1, 'Stephen King', 'Estados Unidos'),
(2, 'Bram Stoker', 'Irlanda'),
(3, 'Shirley Jackson', 'Estados Unidos'),
(4, 'Mary Shelley', 'Reino Unido'),
(5, 'J. Sheridan Le Fanu', 'Irlanda'),
(6, 'Oscar Wilde', 'Irlanda'),
(7, 'Richard Matheson', 'Estados Unidos'),
(8, 'Frank Herbert', 'Estados Unidos'),
(9, 'William Gibson', 'Estados Unidos'),
(10, 'Isaac Asimov', 'Estados Unidos'),
(11, 'George Orwell', 'Reino Unido'),
(12, 'Aldous Huxley', 'Reino Unido'),
(13, 'Stanisław Lem', 'Polônia'),
(14, 'J. R. R. Tolkien', 'Reino Unido'),
(15, 'Patrick Rothfuss', 'Estados Unidos'),
(16, 'George R. R. Martin', 'Estados Unidos'),
(17, 'Brandon Sanderson', 'Estados Unidos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome`, `descricao`) VALUES
(1, 'Terror', 'Horror gótico, sobrenatural e psicológico'),
(2, 'Ficção científica', 'Futuros, tecnologia e distopias'),
(3, 'Fantasia', 'Mundos épicos, magia e mitologia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conta`
--

CREATE TABLE `conta` (
  `id_conta` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('admin','leitor') NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `conta`
--

INSERT INTO `conta` (`id_conta`, `email`, `senha_hash`, `perfil`, `id_usuario`, `id_funcionario`, `ativo`, `criado_em`) VALUES
(1, 'jorge@gmail.com', '$2y$10$TP8wDJ6nEb7AI.AYjeMBruXtvtuaDc2.1h1eeyl6tusWWwP0jQ34q', 'leitor', 4, NULL, 1, '2026-11-16 16:55:00'),
(2, 'admin@biblioteca.com', '$2y$10$zDwCnaT.v/d7kEnAbCb8Je421n.APjNHVjEo9.6WrRbqbDfpwddKS', 'admin', NULL, NULL, 1, '2026-11-16 17:06:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `editora`
--

CREATE TABLE `editora` (
  `id_editora` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `editora`
--

INSERT INTO `editora` (`id_editora`, `nome`, `telefone`) VALUES
(1, 'Suma', NULL),
(2, 'Darkside', NULL),
(3, 'Aleph', NULL),
(4, 'Companhia das Letras', NULL),
(5, 'Biblioteca Azul', NULL),
(6, 'HarperCollins', NULL),
(7, 'Arqueiro', NULL),
(8, 'Leya', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimo`
--

CREATE TABLE `emprestimo` (
  `id_emprestimo` int(11) NOT NULL,
  `data_emprestimo` date NOT NULL DEFAULT curdate(),
  `data_prevista` date NOT NULL,
  `data_devolucao` date DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cargo` varchar(80) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id_funcionario`, `nome`, `cargo`, `telefone`, `ativo`) VALUES
(1, 'Mariana Costa', 'Bibliotecária', '(21) 90000-0001', 1),
(2, 'Pedro Almeida', 'Atendente', '(21) 90000-0002', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `item_emprestimo`
--

CREATE TABLE `item_emprestimo` (
  `id_emprestimo` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `livro`
--

CREATE TABLE `livro` (
  `id_livro` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `ano_publicacao` smallint(6) DEFAULT NULL,
  `estoque` int(11) NOT NULL DEFAULT 1,
  `preco_reposicao` decimal(10,2) NOT NULL DEFAULT 50.00,
  `idioma` varchar(40) NOT NULL DEFAULT 'Português',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `id_editora` int(11) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL
) ;

--
-- Despejando dados para a tabela `livro`
--

INSERT INTO `livro` (`id_livro`, `titulo`, `isbn`, `ano_publicacao`, `estoque`, `preco_reposicao`, `idioma`, `ativo`, `criado_em`, `id_editora`, `id_categoria`) VALUES
(1, 'O Iluminado', NULL, 1977, 2, 55.00, 'Português', 1, '2026-11-16 16:46:44', 1, 1),
(2, 'It: A Coisa', NULL, 1986, 1, 60.00, 'Português', 1, '2026-11-16 16:46:44', 1, 1),
(3, 'Drácula', NULL, 1897, 2, 50.00, 'Português', 1, '2026-11-16 16:46:44', 2, 1),
(4, 'A Assombração da Casa da Colina', NULL, 1959, 1, 45.00, 'Português', 1, '2026-11-16 16:46:44', 1, 1),
(5, 'Frankenstein', NULL, 1818, 2, 50.00, 'Português', 1, '2026-11-16 16:46:44', 2, 1),
(6, 'Carmilla', NULL, 1872, 1, 40.00, 'Português', 1, '2026-11-16 16:46:44', 2, 1),
(7, 'O Retrato de Dorian Gray', NULL, 1890, 2, 45.00, 'Português', 1, '2026-11-16 16:46:44', 4, 1),
(8, 'Eu Sou a Lenda', NULL, 1954, 1, 50.00, 'Português', 1, '2026-11-16 16:46:44', 3, 1),
(9, 'Carrie', NULL, 1974, 2, 50.00, 'Português', 1, '2026-11-16 16:46:44', 1, 1),
(10, 'Duna', NULL, 1965, 2, 65.00, 'Português', 1, '2026-11-16 16:46:44', 3, 2),
(11, 'Neuromancer', NULL, 1984, 1, 55.00, 'Português', 1, '2026-11-16 16:46:44', 3, 2),
(12, 'Fundação', NULL, 1951, 2, 55.00, 'Português', 1, '2026-11-16 16:46:44', 3, 2),
(13, 'Eu, Robô', NULL, 1950, 1, 45.00, 'Português', 1, '2026-11-16 16:46:44', 3, 2),
(14, '1984', NULL, 1949, 3, 50.00, 'Português', 1, '2026-11-16 16:46:44', 4, 2),
(15, 'Admirável Mundo Novo', NULL, 1932, 2, 45.00, 'Português', 1, '2026-11-16 16:46:44', 5, 2),
(16, 'Solaris', NULL, 1961, 1, 50.00, 'Português', 1, '2026-11-16 16:46:44', 3, 2),
(17, 'O Hobbit', NULL, 1937, 2, 55.00, 'Português', 1, '2026-11-16 16:46:44', 6, 3),
(18, 'A Sociedade do Anel', NULL, 1954, 1, 60.00, 'Português', 1, '2026-11-16 16:46:44', 6, 3),
(19, 'O Nome do Vento', NULL, 2007, 2, 84.90, 'Português', 1, '2026-11-16 16:46:44', 7, 3),
(20, 'A Guerra dos Tronos', NULL, 1996, 2, 60.00, 'Português', 1, '2026-11-16 16:46:44', 1, 3),
(21, 'Mistborn: O Império Final', NULL, 2006, 1, 55.00, 'Português', 1, '2026-11-16 16:46:44', 8, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `livro_autor`
--

CREATE TABLE `livro_autor` (
  `id_livro` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `livro_autor`
--

INSERT INTO `livro_autor` (`id_livro`, `id_autor`) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 3),
(5, 4),
(6, 5),
(7, 6),
(8, 7),
(9, 1),
(10, 8),
(11, 9),
(12, 10),
(13, 10),
(14, 11),
(15, 12),
(16, 13),
(17, 14),
(18, 14),
(19, 15),
(20, 16),
(21, 17);

-- --------------------------------------------------------

--
-- Estrutura para tabela `livro_midia`
--

CREATE TABLE `livro_midia` (
  `id_livro` int(11) NOT NULL,
  `id_midia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `livro_midia`
--

INSERT INTO `livro_midia` (`id_livro`, `id_midia`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(5, 13),
(5, 14),
(5, 15),
(5, 16),
(10, 17),
(10, 18),
(18, 23),
(18, 24),
(20, 21),
(20, 22);

-- --------------------------------------------------------

--
-- Estrutura para tabela `midia`
--

CREATE TABLE `midia` (
  `id_midia` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `tipo` varchar(20) NOT NULL
) ;

--
-- Despejando dados para a tabela `midia`
--

INSERT INTO `midia` (`id_midia`, `titulo`, `tipo`) VALUES
(22, 'A Casa do Dragão', 'Série'),
(3, 'Alan Wake 2', 'Jogo'),
(15, 'Bloodborne', 'Jogo'),
(11, 'Castlevania: Symphony of the Night', 'Jogo'),
(19, 'Cyberpunk 2077', 'Jogo'),
(8, 'Dead by Daylight', 'Jogo'),
(2, 'Doutor Sono', 'Filme'),
(10, 'Drácula (BBC, 2020)', 'Série'),
(17, 'Duna: Parte Dois', 'Filme'),
(18, 'Duna: Profecia', 'Série'),
(13, 'Frankenstein (1931)', 'Filme'),
(21, 'Game of Thrones', 'Série'),
(5, 'It: A Coisa (2017)', 'Filme'),
(4, 'Layers of Fear', 'Jogo'),
(9, 'Nosferatu (2024)', 'Filme'),
(1, 'O Iluminado (1980)', 'Filme'),
(23, 'O Senhor dos Anéis: A Sociedade do Anel', 'Filme'),
(24, 'Os Anéis de Poder', 'Série'),
(14, 'Pobres Criaturas', 'Filme'),
(16, 'SOMA', 'Jogo'),
(6, 'Stranger Things', 'Série'),
(20, 'The Witcher 3', 'Jogo'),
(7, 'Until Dawn', 'Jogo'),
(12, 'Vampyr', 'Jogo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_devolucao`
--

CREATE TABLE `pedido_devolucao` (
  `id_pedido_devolucao` int(11) NOT NULL,
  `id_emprestimo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `status` enum('pendente','confirmado','recusado') NOT NULL DEFAULT 'pendente',
  `solicitado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `analisado_em` datetime DEFAULT NULL,
  `observacao` varchar(500) DEFAULT NULL,
  `id_conta_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_emprestimo`
--

CREATE TABLE `pedido_emprestimo` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `status` enum('pendente','processando','aprovado','recusado','cancelado') NOT NULL DEFAULT 'pendente',
  `solicitado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `analisado_em` datetime DEFAULT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `id_emprestimo` int(11) DEFAULT NULL,
  `observacao` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `telefone`, `data_nascimento`, `ativo`) VALUES
(1, 'Ana Souza', 'ana.souza@exemplo.com', '(21) 98888-7777', '2001-04-15', 1),
(2, 'Carlos Lima', 'carlos.lima@exemplo.com', '(21) 97777-6666', '1998-09-22', 1),
(3, 'Karla Mendes', 'karla.mendes@exemplo.com', '(21) 96666-5555', '2000-12-03', 1),
(4, 'Jorge', 'jorge@gmail.com', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_preferencia`
--

CREATE TABLE `usuario_preferencia` (
  `id_preferencia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`preferencias`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_preferencia`
--

INSERT INTO `usuario_preferencia` (`id_preferencia`, `id_usuario`, `preferencias`) VALUES
(1, 1, '{\"generos\": [\"Terror\", \"Fantasia\"], \"midias\": [\"Jogo\", \"Filme\"], \"notificacoes\": {\"email\": true, \"prazo_dias\": 2}}'),
(2, 2, '{\"generos\": [\"Ficção científica\"], \"midias\": [\"Série\"], \"notificacoes\": {\"email\": false, \"prazo_dias\": 1}}'),
(3, 3, '{\"generos\": [\"Terror\"], \"midias\": [\"Jogo\"], \"notificacoes\": {\"email\": true, \"prazo_dias\": 3}}');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_livro_disponivel`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_livro_disponivel` (
`id_livro` int(11)
,`titulo` varchar(200)
,`isbn` varchar(20)
,`ano_publicacao` smallint(6)
,`estoque` int(11)
,`preco_reposicao` decimal(10,2)
,`idioma` varchar(40)
,`ativo` tinyint(1)
,`id_editora` int(11)
,`id_categoria` int(11)
,`disponivel` bigint(22)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_livro_disponivel`
--
DROP TABLE IF EXISTS `vw_livro_disponivel`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_livro_disponivel`  AS SELECT `l`.`id_livro` AS `id_livro`, `l`.`titulo` AS `titulo`, `l`.`isbn` AS `isbn`, `l`.`ano_publicacao` AS `ano_publicacao`, `l`.`estoque` AS `estoque`, `l`.`preco_reposicao` AS `preco_reposicao`, `l`.`idioma` AS `idioma`, `l`.`ativo` AS `ativo`, `l`.`id_editora` AS `id_editora`, `l`.`id_categoria` AS `id_categoria`, `l`.`estoque`- count(case when `e`.`data_devolucao` is null then `e`.`id_emprestimo` end) AS `disponivel` FROM ((`livro` `l` left join `item_emprestimo` `i` on(`i`.`id_livro` = `l`.`id_livro`)) left join `emprestimo` `e` on(`e`.`id_emprestimo` = `i`.`id_emprestimo`)) GROUP BY `l`.`id_livro`, `l`.`titulo`, `l`.`isbn`, `l`.`ano_publicacao`, `l`.`estoque`, `l`.`preco_reposicao`, `l`.`idioma`, `l`.`ativo`, `l`.`id_editora`, `l`.`id_categoria` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `autor`
--
ALTER TABLE `autor`
  ADD PRIMARY KEY (`id_autor`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `conta`
--
ALTER TABLE `conta`
  ADD PRIMARY KEY (`id_conta`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `editora`
--
ALTER TABLE `editora`
  ADD PRIMARY KEY (`id_editora`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `emprestimo`
--
ALTER TABLE `emprestimo`
  ADD PRIMARY KEY (`id_emprestimo`),
  ADD KEY `idx_emprestimo_usuario` (`id_usuario`),
  ADD KEY `idx_emprestimo_funcionario` (`id_funcionario`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id_funcionario`);

--
-- Índices de tabela `item_emprestimo`
--
ALTER TABLE `item_emprestimo`
  ADD PRIMARY KEY (`id_emprestimo`,`id_livro`),
  ADD KEY `idx_item_emprestimo_livro` (`id_livro`);

--
-- Índices de tabela `livro`
--
ALTER TABLE `livro`
  ADD PRIMARY KEY (`id_livro`),
  ADD UNIQUE KEY `titulo` (`titulo`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_livro_editora` (`id_editora`),
  ADD KEY `idx_livro_categoria` (`id_categoria`);

--
-- Índices de tabela `livro_autor`
--
ALTER TABLE `livro_autor`
  ADD PRIMARY KEY (`id_livro`,`id_autor`),
  ADD KEY `idx_livro_autor_autor` (`id_autor`);

--
-- Índices de tabela `livro_midia`
--
ALTER TABLE `livro_midia`
  ADD PRIMARY KEY (`id_livro`,`id_midia`),
  ADD KEY `idx_livro_midia_midia` (`id_midia`);

--
-- Índices de tabela `midia`
--
ALTER TABLE `midia`
  ADD PRIMARY KEY (`id_midia`),
  ADD UNIQUE KEY `uk_midia_titulo_tipo` (`titulo`,`tipo`);

--
-- Índices de tabela `pedido_devolucao`
--
ALTER TABLE `pedido_devolucao`
  ADD PRIMARY KEY (`id_pedido_devolucao`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_conta_admin` (`id_conta_admin`),
  ADD KEY `idx_devolucao_status` (`status`,`solicitado_em`),
  ADD KEY `idx_devolucao_emprestimo` (`id_emprestimo`,`status`);

--
-- Índices de tabela `pedido_emprestimo`
--
ALTER TABLE `pedido_emprestimo`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `id_emprestimo` (`id_emprestimo`),
  ADD KEY `fk_pedido_livro` (`id_livro`),
  ADD KEY `fk_pedido_funcionario` (`id_funcionario`),
  ADD KEY `idx_pedido_status` (`status`,`solicitado_em`),
  ADD KEY `idx_pedido_usuario` (`id_usuario`,`status`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `usuario_preferencia`
--
ALTER TABLE `usuario_preferencia`
  ADD PRIMARY KEY (`id_preferencia`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `autor`
--
ALTER TABLE `autor`
  MODIFY `id_autor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `conta`
--
ALTER TABLE `conta`
  MODIFY `id_conta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `editora`
--
ALTER TABLE `editora`
  MODIFY `id_editora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `emprestimo`
--
ALTER TABLE `emprestimo`
  MODIFY `id_emprestimo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `livro`
--
ALTER TABLE `livro`
  MODIFY `id_livro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `midia`
--
ALTER TABLE `midia`
  MODIFY `id_midia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedido_devolucao`
--
ALTER TABLE `pedido_devolucao`
  MODIFY `id_pedido_devolucao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedido_emprestimo`
--
ALTER TABLE `pedido_emprestimo`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuario_preferencia`
--
ALTER TABLE `usuario_preferencia`
  MODIFY `id_preferencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `conta`
--
ALTER TABLE `conta`
  ADD CONSTRAINT `fk_conta_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`),
  ADD CONSTRAINT `fk_conta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `emprestimo`
--
ALTER TABLE `emprestimo`
  ADD CONSTRAINT `fk_emprestimo_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`),
  ADD CONSTRAINT `fk_emprestimo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `item_emprestimo`
--
ALTER TABLE `item_emprestimo`
  ADD CONSTRAINT `fk_item_emprestimo` FOREIGN KEY (`id_emprestimo`) REFERENCES `emprestimo` (`id_emprestimo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_livro` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`);

--
-- Restrições para tabelas `livro`
--
ALTER TABLE `livro`
  ADD CONSTRAINT `fk_livro_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `fk_livro_editora` FOREIGN KEY (`id_editora`) REFERENCES `editora` (`id_editora`);

--
-- Restrições para tabelas `livro_autor`
--
ALTER TABLE `livro_autor`
  ADD CONSTRAINT `fk_la_autor` FOREIGN KEY (`id_autor`) REFERENCES `autor` (`id_autor`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_la_livro` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`) ON DELETE CASCADE;

--
-- Restrições para tabelas `livro_midia`
--
ALTER TABLE `livro_midia`
  ADD CONSTRAINT `fk_lm_livro` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lm_midia` FOREIGN KEY (`id_midia`) REFERENCES `midia` (`id_midia`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pedido_devolucao`
--
ALTER TABLE `pedido_devolucao`
  ADD CONSTRAINT `pedido_devolucao_ibfk_1` FOREIGN KEY (`id_emprestimo`) REFERENCES `emprestimo` (`id_emprestimo`),
  ADD CONSTRAINT `pedido_devolucao_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `pedido_devolucao_ibfk_3` FOREIGN KEY (`id_conta_admin`) REFERENCES `conta` (`id_conta`);

--
-- Restrições para tabelas `pedido_emprestimo`
--
ALTER TABLE `pedido_emprestimo`
  ADD CONSTRAINT `fk_pedido_emprestimo` FOREIGN KEY (`id_emprestimo`) REFERENCES `emprestimo` (`id_emprestimo`),
  ADD CONSTRAINT `fk_pedido_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`),
  ADD CONSTRAINT `fk_pedido_livro` FOREIGN KEY (`id_livro`) REFERENCES `livro` (`id_livro`),
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `usuario_preferencia`
--
ALTER TABLE `usuario_preferencia`
  ADD CONSTRAINT `fk_preferencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
