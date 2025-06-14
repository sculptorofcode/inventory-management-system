-- Migrations Table
-- This table tracks which migrations have been executed

CREATE TABLE IF NOT EXISTS `tbl_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL COMMENT 'Migration filename',
  `batch` int(11) NOT NULL COMMENT 'Migration batch number',
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;