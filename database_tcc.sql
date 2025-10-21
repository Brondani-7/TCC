-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21/10/2025 às 16:40
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `database_tcc`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `commentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `comentarios`
--

INSERT INTO `comentarios` (`commentID`, `UserID`, `text`, `date`) VALUES
(1, 2, 'Concordo! O multiplayer está muito bom.', '2025-10-20'),
(2, 3, 'A trilha sonora de FF é maravilhosa.', '2025-10-20'),
(3, 1, 'F1 2025 me surpreendeu com os detalhes.', '2025-10-20'),
(4, 2, 'Age of Empires sempre foi meu favorito.', '2025-10-20'),
(5, 3, 'Hollow Knight merece uma continuação.', '2025-10-20'),
(6, 7, 'Battlefield é ótimo para jogar com amigos.', '2025-10-21'),
(7, 8, 'The Witcher tem uma narrativa incrível.', '2025-10-21'),
(8, 4, 'Gran Turismo me ensinou sobre carros!', '2025-10-21'),
(9, 5, 'Civilization me prende por horas.', '2025-10-21'),
(10, 6, 'Celeste tem uma trilha sonora maravilhosa.', '2025-10-21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fóruns`
--

CREATE TABLE `fóruns` (
  `ForumID` int(11) UNSIGNED NOT NULL,
  `ForumName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fóruns`
--

INSERT INTO `fóruns` (`ForumID`, `ForumName`, `CustomerID`, `PostID`) VALUES
(1, 'Ação: Call of Duty', 1, 1),
(2, 'RPG: Final Fantasy', 2, 2),
(3, 'Corrida: F1 2025', 3, 3),
(4, 'Estratégia: Age of Empires', 1, 4),
(5, 'Indie: Hollow Knight', 2, 5),
(6, 'Ação: Battlefield', 6, 6),
(7, 'RPG: The Witcher', 7, 7),
(8, 'Corrida: Gran Turismo', 8, 8),
(9, 'Estratégia: Civilization VI', 4, 9),
(10, 'Indie: Celeste', 5, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE `posts` (
  `PostID` int(11) UNSIGNED NOT NULL,
  `UserID` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `content` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `likes` int(11) NOT NULL,
  `dislikes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `posts`
--

INSERT INTO `posts` (`PostID`, `UserID`, `time`, `content`, `tags`, `likes`, `dislikes`) VALUES
(1, 1, '2025-10-21 13:51:17', 'Call of Duty está incrível esse ano!', 'ação, fps', 10, 2),
(2, 2, '2025-10-21 13:51:17', 'Final Fantasy XVI tem uma história envolvente.', 'rpg, fantasia', 15, 1),
(3, 3, '2025-10-21 13:51:17', 'F1 2025 trouxe gráficos realistas e física aprimorada.', 'corrida, simulação', 8, 0),
(4, 1, '2025-10-21 13:51:17', 'Age of Empires IV é um retorno às origens.', 'estratégia, histórico', 12, 3),
(5, 2, '2025-10-21 13:51:17', 'Hollow Knight é uma obra-prima indie.', 'indie, metroidvania', 20, 0),
(6, 6, '2025-10-21 14:00:54', 'Battlefield tem mapas gigantes e muita ação!', 'ação, fps, multiplayer', 9, 1),
(7, 7, '2025-10-21 14:00:54', 'The Witcher 3 é um dos melhores RPGs já feitos.', 'rpg, mundo aberto', 18, 2),
(8, 8, '2025-10-21 14:00:54', 'Gran Turismo é perfeito para quem ama simulação realista.', 'corrida, carros', 11, 0),
(9, 4, '2025-10-21 14:00:54', 'Civilization VI exige estratégia profunda e planejamento.', 'estratégia, turnos', 14, 1),
(10, 5, '2025-10-21 14:00:54', 'Celeste é desafiador e emocional ao mesmo tempo.', 'indie, plataforma', 17, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuários`
--

CREATE TABLE `usuários` (
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuários`
--

INSERT INTO `usuários` (`CustomerID`, `Email`, `Name`, `Password`) VALUES
(1, 'a@a', 'alan', '$2y$10$drtrCibtU5EZwQsH7tC4/uIwoXw9IynYFtmD7kXlhcISLVs8t9a3q'),
(2, 'alice@gamer.com', 'Alice', 'senha123'),
(3, 'bob@rpg.com', 'Bob', 'senha456'),
(4, 'carol@corrida.com', 'Carol', 'senha789'),
(5, 'diana@estrategia.com', 'Diana', 'senha321'),
(6, 'eric@indie.com', 'Eric', 'senha654'),
(7, 'fernanda@acao.com', 'Fernanda', 'senha987'),
(8, 'gustavo@rpg.com', 'Gustavo', 'senhaabc'),
(9, 'helena@corrida.com', 'Helena', 'senhaxyz');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`commentID`);

--
-- Índices de tabela `fóruns`
--
ALTER TABLE `fóruns`
  ADD PRIMARY KEY (`ForumID`);

--
-- Índices de tabela `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`PostID`);

--
-- Índices de tabela `usuários`
--
ALTER TABLE `usuários`
  ADD PRIMARY KEY (`CustomerID`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `commentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `fóruns`
--
ALTER TABLE `fóruns`
  MODIFY `ForumID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuários`
--
ALTER TABLE `usuários`
  MODIFY `CustomerID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
