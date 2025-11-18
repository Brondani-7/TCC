-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/11/2025 às 13:57
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
-- Estrutura para tabela `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `NotificationType` enum('social','games','achievements','system') DEFAULT 'social',
  `NotificationText` text NOT NULL,
  `IsRead` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE `posts` (
  `PostID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `Data` datetime DEFAULT current_timestamp(),
  `PostMessage` text DEFAULT NULL,
  `Likes` int(11) DEFAULT 0,
  `Retweets` int(11) DEFAULT 0,
  `Comments` int(11) DEFAULT 0
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
(2, 'pobre@pobre.com', 'pobre', '$2y$10$oXz6UJ8P6nSQMgjPRPTPBuJqd3e4KJZHM.rd5tRvpc7AFOTspys/W', 'pobre', NULL, '🔥', NULL, NULL, '2025-11-11 11:05:55');

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
-- Índices de tabela `foruns`
--
ALTER TABLE `foruns`
  ADD PRIMARY KEY (`ForumID`);

--
-- Índices de tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Índices de tabela `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`PostID`),
  ADD KEY `CustomerID` (`CustomerID`);

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
-- AUTO_INCREMENT de tabela `foruns`
--
ALTER TABLE `foruns`
  MODIFY `ForumID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `CustomerID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `fangames`
--
ALTER TABLE `fangames`
  ADD CONSTRAINT `fangames_ibfk_1` FOREIGN KEY (`DeveloperID`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`);

--
-- Restrições para tabelas `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
