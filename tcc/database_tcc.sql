-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/11/2025 às 15:48
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
-- Estrutura para tabela `fangames`
--

CREATE TABLE `fangames` (
  `GameID` int(11) UNSIGNED NOT NULL,
  `GameTitle` varchar(255) NOT NULL,
  `GameDescription` text DEFAULT NULL,
  `DeveloperID` int(11) UNSIGNED NOT NULL,
  `Downloads` int(11) DEFAULT 0,
  `Rating` decimal(3,2) DEFAULT 0.00,
  `FileSize` varchar(50) DEFAULT NULL,
  `Status` enum('Completo','Em Desenvolvimento','Demo') DEFAULT 'Em Desenvolvimento',
  `Genre` varchar(100) DEFAULT NULL,
  `Franchise` varchar(100) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_categories`
--

CREATE TABLE `forum_categories` (
  `CategoryID` int(11) UNSIGNED NOT NULL,
  `CategoryName` varchar(255) NOT NULL,
  `CategoryDescription` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forum_categories`
--

INSERT INTO `forum_categories` (`CategoryID`, `CategoryName`, `CategoryDescription`, `CreatedAt`) VALUES
(1, 'Geral', 'Discussões gerais sobre fangames e comunidade', '2025-11-18 10:51:24'),
(2, 'Desenvolvimento', 'Dúvidas e discussões sobre desenvolvimento de jogos', '2025-11-18 10:51:24'),
(3, 'Lançamentos', 'Anúncio de novos fangames lançados', '2025-11-18 10:51:24'),
(4, 'Feedback', 'Peça e dê feedback sobre projetos', '2025-11-18 10:51:24'),
(5, 'Recursos', 'Compartilhe recursos úteis para desenvolvedores', '2025-11-18 10:51:24'),
(6, 'Off-Topic', 'Conversas fora do tema principal', '2025-11-18 10:51:24'),
(7, 'Geral', 'Discussões gerais sobre fangames e comunidade', '2025-11-18 11:14:47'),
(8, 'Desenvolvimento', 'Dúvidas e discussões sobre desenvolvimento de jogos', '2025-11-18 11:14:47'),
(9, 'Lançamentos', 'Anúncio de novos fangames lançados', '2025-11-18 11:14:47'),
(10, 'Feedback', 'Peça e dê feedback sobre projetos', '2025-11-18 11:14:47'),
(11, 'Recursos', 'Compartilhe recursos úteis para desenvolvedores', '2025-11-18 11:14:47'),
(12, 'Off-Topic', 'Conversas fora do tema principal', '2025-11-18 11:14:47'),
(13, 'Geral', 'Discussões gerais sobre fangames e comunidade', '2025-11-18 11:21:34'),
(14, 'Desenvolvimento', 'Dúvidas e discussões sobre desenvolvimento de jogos', '2025-11-18 11:21:34'),
(15, 'Lançamentos', 'Anúncio de novos fangames lançados', '2025-11-18 11:21:34'),
(16, 'Feedback', 'Peça e dê feedback sobre projetos', '2025-11-18 11:21:34'),
(17, 'Recursos', 'Compartilhe recursos úteis para desenvolvedores', '2025-11-18 11:21:34'),
(18, 'Off-Topic', 'Conversas fora do tema principal', '2025-11-18 11:21:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_likes`
--

CREATE TABLE `forum_likes` (
  `LikeID` int(11) UNSIGNED NOT NULL,
  `PostID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_posts`
--

CREATE TABLE `forum_posts` (
  `PostID` int(11) UNSIGNED NOT NULL,
  `TopicID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `PostContent` text NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp(),
  `IsEdited` tinyint(1) DEFAULT 0,
  `EditedBy` int(11) UNSIGNED DEFAULT NULL,
  `EditedAt` datetime DEFAULT NULL,
  `LikesCount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forum_posts`
--

INSERT INTO `forum_posts` (`PostID`, `TopicID`, `CustomerID`, `PostContent`, `CreatedAt`, `UpdatedAt`, `IsEdited`, `EditedBy`, `EditedAt`, `LikesCount`) VALUES
(1, 1, 4, 'ola', '2025-11-18 11:20:53', '2025-11-18 11:20:53', 0, NULL, NULL, 0),
(2, 2, 4, 'a', '2025-11-18 11:25:14', '2025-11-18 11:25:14', 0, NULL, NULL, 0),
(3, 2, 4, '[quote=\"pobre da silva\"]\r\n\r\n[/quote]\r\n\r\nasdas', '2025-11-18 11:27:27', '2025-11-18 11:27:27', 0, NULL, NULL, 0),
(4, 2, 4, 'asd', '2025-11-18 11:27:37', '2025-11-18 11:27:37', 0, NULL, NULL, 0),
(5, 2, 4, 'asd', '2025-11-18 11:27:44', '2025-11-18 11:27:44', 0, NULL, NULL, 0),
(6, 2, 4, '[quote=\"pobre da silva\"]\r\n\r\n[/quote]\r\n\r\nasd', '2025-11-18 11:28:13', '2025-11-18 11:28:13', 0, NULL, NULL, 0),
(7, 3, 4, 'asd', '2025-11-18 11:31:29', '2025-11-18 11:31:29', 0, NULL, NULL, 0),
(8, 3, 4, 'a', '2025-11-18 11:31:38', '2025-11-18 11:31:38', 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_posts_old`
--

CREATE TABLE `forum_posts_old` (
  `PostID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `Data` datetime DEFAULT current_timestamp(),
  `PostMessage` text DEFAULT NULL,
  `Likes` int(11) DEFAULT 0,
  `Retweets` int(11) DEFAULT 0,
  `Comments` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forum_posts_old`
--

INSERT INTO `forum_posts_old` (`PostID`, `CustomerID`, `Data`, `PostMessage`, `Likes`, `Retweets`, `Comments`) VALUES
(1, 3, '2025-11-18 10:29:18', 'oasd\r\n', 0, 0, 0),
(2, 3, '2025-11-18 10:29:22', 'oi', 0, 0, 0),
(3, 3, '2025-11-18 10:29:33', 'a', 0, 0, 0),
(4, 4, '2025-11-18 10:31:11', 'asd', 0, 0, 0),
(5, 4, '2025-11-18 10:45:24', 'ola', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `forum_topics`
--

CREATE TABLE `forum_topics` (
  `TopicID` int(11) UNSIGNED NOT NULL,
  `CategoryID` int(11) UNSIGNED NOT NULL,
  `TopicTitle` varchar(255) NOT NULL,
  `TopicDescription` text DEFAULT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp(),
  `IsSticky` tinyint(1) DEFAULT 0,
  `IsLocked` tinyint(1) DEFAULT 0,
  `ViewCount` int(11) DEFAULT 0,
  `ReplyCount` int(11) DEFAULT 0,
  `LastPostBy` int(11) UNSIGNED DEFAULT NULL,
  `LastPostAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forum_topics`
--

INSERT INTO `forum_topics` (`TopicID`, `CategoryID`, `TopicTitle`, `TopicDescription`, `CustomerID`, `CreatedAt`, `UpdatedAt`, `IsSticky`, `IsLocked`, `ViewCount`, `ReplyCount`, `LastPostBy`, `LastPostAt`) VALUES
(1, 2, 'ola', 'ola', 4, '2025-11-18 11:20:53', '2025-11-18 11:20:53', 0, 0, 1, 1, 4, '2025-11-18 11:20:53'),
(2, 2, 'a', 'a', 4, '2025-11-18 11:25:14', '2025-11-18 11:28:13', 0, 0, 10, 5, 4, '2025-11-18 11:28:13'),
(3, 2, 'a', 'as', 4, '2025-11-18 11:31:29', '2025-11-18 11:31:38', 0, 0, 5, 2, 4, '2025-11-18 11:31:38'),
(4, 2, 'as', 'as', 4, '2025-11-18 11:45:53', '2025-11-18 11:45:59', 0, 0, 10, 2, 4, '2025-11-18 11:45:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `foruns`
--

CREATE TABLE `foruns` (
  `ForumID` int(11) UNSIGNED NOT NULL,
  `ForumName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CustomerID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `CustomerGmail` varchar(255) NOT NULL,
  `CustomerName` varchar(255) NOT NULL,
  `CustomerPassword` varchar(255) NOT NULL,
  `CustomerHandle` varchar(255) DEFAULT NULL,
  `CustomerBio` text DEFAULT NULL,
  `ProfileIcon` varchar(10) DEFAULT '?',
  `ProfilePhoto` text DEFAULT NULL,
  `CoverPhoto` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`CustomerID`, `CustomerGmail`, `CustomerName`, `CustomerPassword`, `CustomerHandle`, `CustomerBio`, `ProfileIcon`, `ProfilePhoto`, `CoverPhoto`, `CreatedAt`) VALUES
(1, 'guilherme@gmail.com', 'masterplan', '$2y$10$sZ9KMLyh4JjucZ4zoBywiOyDqAD.llPJGPU5fW/frFecj0gGkLP62', 'leonjud2', 'eu sou o master plan', '🔥', 'uploads/profiles/profile_1_1763467767.jpg', 'uploads/cover_photos/cover_1_1762871705.gif', '2025-11-11 11:02:26'),
(2, 'pobre@pobre.com', 'pobre', '$2y$10$oXz6UJ8P6nSQMgjPRPTPBuJqd3e4KJZHM.rd5tRvpc7AFOTspys/W', 'pobre', NULL, '🔥', NULL, NULL, '2025-11-11 11:05:55'),
(3, 'ze@gmail.com', 'ze', '$2y$10$hIMrkaewCG9Vy2EBdVT61e18bsbGjtYrk0mOb/WlXTf4KMg/oW4P.', 'zebao', NULL, '?', 'uploads/profiles/profile_3_1763472419.png', NULL, '2025-11-18 10:26:07'),
(4, 'pobre2@gmail.com', 'pobre da silva', '$2y$10$Me6PEdx73idNUPwCCfpA0egKn2hGMbkJw8cWkFzIb3mUULIA1xe.C', 'pobre2', 'ola', '?', 'uploads/profiles/profile_4_1763473646.png', NULL, '2025-11-18 10:30:40');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `fangames`
--
ALTER TABLE `fangames`
  ADD PRIMARY KEY (`GameID`),
  ADD KEY `DeveloperID` (`DeveloperID`);

--
-- Índices de tabela `forum_categories`
--
ALTER TABLE `forum_categories`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Índices de tabela `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD PRIMARY KEY (`LikeID`),
  ADD UNIQUE KEY `unique_like` (`PostID`,`CustomerID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Índices de tabela `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`PostID`),
  ADD KEY `TopicID` (`TopicID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `EditedBy` (`EditedBy`);

--
-- Índices de tabela `forum_posts_old`
--
ALTER TABLE `forum_posts_old`
  ADD PRIMARY KEY (`PostID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Índices de tabela `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`TopicID`),
  ADD KEY `CategoryID` (`CategoryID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `LastPostBy` (`LastPostBy`);

--
-- Índices de tabela `foruns`
--
ALTER TABLE `foruns`
  ADD PRIMARY KEY (`ForumID`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `CustomerGmail` (`CustomerGmail`),
  ADD UNIQUE KEY `CustomerHandle` (`CustomerHandle`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `fangames`
--
ALTER TABLE `fangames`
  MODIFY `GameID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `forum_categories`
--
ALTER TABLE `forum_categories`
  MODIFY `CategoryID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `forum_likes`
--
ALTER TABLE `forum_likes`
  MODIFY `LikeID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `forum_posts_old`
--
ALTER TABLE `forum_posts_old`
  MODIFY `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `TopicID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `foruns`
--
ALTER TABLE `foruns`
  MODIFY `ForumID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `CustomerID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `fangames`
--
ALTER TABLE `fangames`
  ADD CONSTRAINT `fangames_ibfk_1` FOREIGN KEY (`DeveloperID`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD CONSTRAINT `forum_likes_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `forum_posts` (`PostID`),
  ADD CONSTRAINT `forum_likes_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`TopicID`) REFERENCES `forum_topics` (`TopicID`),
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`),
  ADD CONSTRAINT `forum_posts_ibfk_3` FOREIGN KEY (`EditedBy`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `forum_posts_old`
--
ALTER TABLE `forum_posts_old`
  ADD CONSTRAINT `forum_posts_old_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `forum_categories` (`CategoryID`),
  ADD CONSTRAINT `forum_topics_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`),
  ADD CONSTRAINT `forum_topics_ibfk_3` FOREIGN KEY (`LastPostBy`) REFERENCES `usuarios` (`CustomerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
