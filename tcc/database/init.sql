-- Atualização da estrutura do banco para segurança
ALTER TABLE `usuários` 
MODIFY COLUMN `CustomerPassword` VARCHAR(255) NOT NULL,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN `last_login` TIMESTAMP NULL,
ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE;

-- Tabela para fangames
CREATE TABLE IF NOT EXISTS `fangames` (
    `game_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `developer_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `genre` VARCHAR(100),
    `franchise` VARCHAR(100),
    `status` ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    `cover_image` VARCHAR(255),
    `file_path` VARCHAR(255),
    `file_size` BIGINT,
    `version` VARCHAR(50),
    `tags` TEXT,
    `download_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`developer_id`) REFERENCES `usuários`(`CustomerID`) ON DELETE CASCADE
);

-- Tabela para avaliações
CREATE TABLE IF NOT EXISTS `ratings` (
    `rating_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT CHECK (rating >= 1 AND rating <= 5),
    `comment` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `fangames`(`game_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuários`(`CustomerID`) ON DELETE CASCADE,
    UNIQUE KEY `unique_rating` (`game_id`, `user_id`)
);

-- Tabela para downloads
CREATE TABLE IF NOT EXISTS `downloads` (
    `download_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `fangames`(`game_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuários`(`CustomerID`) ON DELETE CASCADE
);