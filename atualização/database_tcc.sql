-- database_tcc.sql atualizado
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/08/2025 às 16:41
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
-- Banco de dados: `database_tcc`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `fóruns`
--

CREATE TABLE `foruns` (
  `ForumID` int(11) UNSIGNED NOT NULL,
  `ForumName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CustomerID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL
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
-- Estrutura para tabela `usuários`
--

CREATE TABLE `usuarios` (
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `CustomerGmail` varchar(255) NOT NULL,
  `CustomerName` varchar(255) NOT NULL,
  `CustomerPassword` varchar(255) NOT NULL,
  `CustomerHandle` varchar(255) DEFAULT NULL,
  `CustomerBio` text DEFAULT NULL,
  `ProfileIcon` varchar(10) DEFAULT '🔥',
  `ProfilePhoto` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `foruns`
--
ALTER TABLE `foruns`
  ADD PRIMARY KEY (`ForumID`);

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
  ADD UNIQUE KEY `CustomerHandle` (`CustomerHandle`),
ADD COLUMN ProfileBanner TEXT NULL AFTER ProfilePhoto,
ADD COLUMN Location VARCHAR(100) NULL AFTER CustomerBio,
ADD COLUMN Website VARCHAR(255) NULL AFTER Location,
ADD COLUMN UpdatedAt DATETIME NULL AFTER CreatedAt;

--
-- Índices de tabela `fangames`
--
ALTER TABLE `fangames`
  ADD PRIMARY KEY (`GameID`),
  ADD KEY `DeveloperID` (`DeveloperID`);

--
-- Índices de tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `foruns`
--
ALTER TABLE `foruns`
  MODIFY `ForumID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
MODIFY `CustomerID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ADD COLUMN `ProfileBanner` text DEFAULT NULL AFTER `ProfilePhoto`,
ADD COLUMN `Location` varchar(100) DEFAULT NULL AFTER `CustomerBio`,
ADD COLUMN `Website` varchar(255) DEFAULT NULL AFTER `Location`,
ADD COLUMN `TwitterHandle` varchar(100) DEFAULT NULL AFTER `Website`;

--
-- AUTO_INCREMENT de tabela `fangames`
--
ALTER TABLE `fangames`
  MODIFY `GameID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`);

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
COMMIT;

-- Adicione esta coluna à tabela fangames se ainda não existir
ALTER TABLE `fangames` 
ADD COLUMN IF NOT EXISTS `GameFile` TEXT NULL AFTER `FileSize`;

-- Se precisar criar as tabelas de tags (opcional)
CREATE TABLE IF NOT EXISTS `game_tags` (
  `TagID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `TagName` varchar(50) NOT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`TagID`),
  UNIQUE KEY `TagName` (`TagName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `game_tag_relations` (
  `RelationID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `TagID` int(11) UNSIGNED NOT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`RelationID`),
  FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE,
  FOREIGN KEY (`TagID`) REFERENCES `game_tags` (`TagID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;