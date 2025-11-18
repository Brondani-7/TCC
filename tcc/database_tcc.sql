-- database_tcc.sql atualizado e corrigido
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
-- Estrutura para tabela `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `CustomerID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `CustomerGmail` varchar(255) NOT NULL,
  `CustomerName` varchar(255) NOT NULL,
  `CustomerPassword` varchar(255) NOT NULL,
  `CustomerHandle` varchar(255) DEFAULT NULL,
  `CustomerBio` text DEFAULT NULL,
  `ProfileIcon` varchar(10) DEFAULT '🔥',
  `ProfilePhoto` text DEFAULT NULL,
  `ProfileBanner` text DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Website` varchar(255) DEFAULT NULL,
  `TwitterHandle` varchar(100) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`CustomerID`),
  UNIQUE KEY `CustomerGmail` (`CustomerGmail`),
  UNIQUE KEY `CustomerHandle` (`CustomerHandle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fangames`
--

CREATE TABLE IF NOT EXISTS `fangames` (
  `GameID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameTitle` varchar(255) NOT NULL,
  `GameDescription` text DEFAULT NULL,
  `DeveloperID` int(11) UNSIGNED NOT NULL,
  `Franchise` varchar(100) DEFAULT NULL,
  `Genre` varchar(100) DEFAULT NULL,
  `Status` enum('Em Desenvolvimento','Lançado','Pausado','Cancelado') DEFAULT 'Em Desenvolvimento',
  `Tags` text DEFAULT NULL,
  `GameFile` text DEFAULT NULL,
  `GameCover` text DEFAULT NULL,
  `DownloadLink` text DEFAULT NULL,
  `SystemRequirements` text DEFAULT NULL,
  `ReleaseDate` date DEFAULT NULL,
  `Downloads` int(11) DEFAULT 0,
  `Rating` decimal(3,2) DEFAULT 0.00,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`GameID`),
  KEY `DeveloperID` (`DeveloperID`),
  KEY `idx_franchise` (`Franchise`),
  KEY `idx_genre` (`Genre`),
  KEY `idx_status` (`Status`),
  KEY `idx_created_at` (`CreatedAt`),
  CONSTRAINT `fangames_ibfk_1` FOREIGN KEY (`DeveloperID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_screenshots`
--

CREATE TABLE IF NOT EXISTS `game_screenshots` (
  `ScreenshotID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `ImagePath` text NOT NULL,
  `Caption` varchar(255) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ScreenshotID`),
  KEY `GameID` (`GameID`),
  CONSTRAINT `game_screenshots_ibfk_1` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `PostID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `GameID` int(11) UNSIGNED DEFAULT NULL,
  `Data` datetime DEFAULT current_timestamp(),
  `PostMessage` text DEFAULT NULL,
  `Likes` int(11) DEFAULT 0,
  `Retweets` int(11) DEFAULT 0,
  `Comments` int(11) DEFAULT 0,
  `PostType` enum('text','game_update','screenshot','review') DEFAULT 'text',
  `IsPinned` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`PostID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `GameID` (`GameID`),
  KEY `idx_post_type` (`PostType`),
  KEY `idx_pinned` (`IsPinned`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `NotificationID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `NotificationType` enum('social','games','achievements','system') DEFAULT 'social',
  `NotificationText` text NOT NULL,
  `RelatedID` int(11) UNSIGNED DEFAULT NULL,
  `RelatedType` enum('post','game','comment','follow') DEFAULT NULL,
  `IsRead` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`NotificationID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `idx_notification_type` (`NotificationType`),
  KEY `idx_is_read` (`IsRead`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_ratings`
--

CREATE TABLE IF NOT EXISTS `game_ratings` (
  `RatingID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `Rating` decimal(2,1) DEFAULT 0.0 CHECK (`Rating` >= 0 AND `Rating` <= 5),
  `Review` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`RatingID`),
  UNIQUE KEY `unique_game_user_rating` (`GameID`,`CustomerID`),
  KEY `CustomerID` (`CustomerID`),
  CONSTRAINT `game_ratings_ibfk_1` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE,
  CONSTRAINT `game_ratings_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_comments`
--

CREATE TABLE IF NOT EXISTS `game_comments` (
  `CommentID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `ParentCommentID` int(11) UNSIGNED DEFAULT NULL,
  `CommentText` text NOT NULL,
  `Likes` int(11) DEFAULT 0,
  `IsEdited` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`CommentID`),
  KEY `GameID` (`GameID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `ParentCommentID` (`ParentCommentID`),
  CONSTRAINT `game_comments_ibfk_1` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE,
  CONSTRAINT `game_comments_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE,
  CONSTRAINT `game_comments_ibfk_3` FOREIGN KEY (`ParentCommentID`) REFERENCES `game_comments` (`CommentID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_follows`
--

CREATE TABLE IF NOT EXISTS `user_follows` (
  `FollowID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `FollowerID` int(11) UNSIGNED NOT NULL,
  `FollowingID` int(11) UNSIGNED NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`FollowID`),
  UNIQUE KEY `unique_follow` (`FollowerID`,`FollowingID`),
  KEY `FollowerID` (`FollowerID`),
  KEY `FollowingID` (`FollowingID`),
  CONSTRAINT `user_follows_ibfk_1` FOREIGN KEY (`FollowerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE,
  CONSTRAINT `user_follows_ibfk_2` FOREIGN KEY (`FollowingID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_downloads`
--

CREATE TABLE IF NOT EXISTS `game_downloads` (
  `DownloadID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `UserAgent` text DEFAULT NULL,
  `DownloadedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`DownloadID`),
  KEY `GameID` (`GameID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `idx_download_date` (`DownloadedAt`),
  CONSTRAINT `game_downloads_ibfk_1` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE,
  CONSTRAINT `game_downloads_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_views`
--

CREATE TABLE IF NOT EXISTS `game_views` (
  `ViewID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `GameID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(11) UNSIGNED DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `ViewedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ViewID`),
  KEY `GameID` (`GameID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `idx_view_date` (`ViewedAt`),
  CONSTRAINT `game_views_ibfk_1` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE,
  CONSTRAINT `game_views_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_favorites`
--

CREATE TABLE IF NOT EXISTS `user_favorites` (
  `FavoriteID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) UNSIGNED NOT NULL,
  `GameID` int(11) UNSIGNED NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`FavoriteID`),
  UNIQUE KEY `unique_user_game_favorite` (`CustomerID`,`GameID`),
  KEY `GameID` (`GameID`),
  CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `usuarios` (`CustomerID`) ON DELETE CASCADE,
  CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`GameID`) REFERENCES `fangames` (`GameID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices e restrições adicionais
--

-- Índices para otimização de buscas
ALTER TABLE `fangames` ADD FULLTEXT KEY `ft_game_search` (`GameTitle`,`GameDescription`,`Tags`);
ALTER TABLE `posts` ADD FULLTEXT KEY `ft_post_search` (`PostMessage`);

-- Procedimento para atualizar a média de ratings automaticamente
DELIMITER $$
CREATE TRIGGER `update_game_rating` AFTER INSERT ON `game_ratings`
FOR EACH ROW
BEGIN
    UPDATE fangames 
    SET Rating = (
        SELECT AVG(Rating) 
        FROM game_ratings 
        WHERE GameID = NEW.GameID
    ),
    UpdatedAt = NOW()
    WHERE GameID = NEW.GameID;
END$$
DELIMITER ;

-- Trigger para incrementar downloads
DELIMITER $$
CREATE TRIGGER `increment_game_downloads` AFTER INSERT ON `game_downloads`
FOR EACH ROW
BEGIN
    UPDATE fangames 
    SET Downloads = Downloads + 1,
    UpdatedAt = NOW()
    WHERE GameID = NEW.GameID;
END$$
DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;