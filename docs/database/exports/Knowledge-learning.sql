CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `roles` varchar(255) NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `is_verified` boolean NOT NULL DEFAULT false,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `themes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `cursus` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `theme_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price` int NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `lessons` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `cursus_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `video_url` varchar(255),
  `position` int NOT NULL,
  `price` int NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `purchases` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int,
  `lesson_id` int,
  `amount` int NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'EUR',
  `status` varchar(255) NOT NULL,
  `stripe_session_id` varchar(255),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `access_rights` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int,
  `lesson_id` int,
  `granted_at` datetime NOT NULL,
  `purchase_id` int,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `lesson_validations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `cursus_validations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `certifications` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `theme_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE INDEX `purchases_index_0` ON `purchases` (`user_id`);

CREATE INDEX `purchases_index_1` ON `purchases` (`cursus_id`);

CREATE INDEX `purchases_index_2` ON `purchases` (`lesson_id`);

CREATE UNIQUE INDEX `purchases_index_3` ON `purchases` (`stripe_session_id`);

CREATE INDEX `access_rights_index_4` ON `access_rights` (`user_id`);

CREATE INDEX `access_rights_index_5` ON `access_rights` (`cursus_id`);

CREATE INDEX `access_rights_index_6` ON `access_rights` (`lesson_id`);

CREATE UNIQUE INDEX `access_rights_index_7` ON `access_rights` (`user_id`, `cursus_id`);

CREATE UNIQUE INDEX `access_rights_index_8` ON `access_rights` (`user_id`, `cursus_id`);

CREATE UNIQUE INDEX `lesson_validations_index_9` ON `lesson_validations` (`user_id`, `lesson_id`);

CREATE UNIQUE INDEX `cursus_validations_index_10` ON `cursus_validations` (`user_id`, `cursus_id`);

CREATE UNIQUE INDEX `certifications_index_11` ON `certifications` (`user_id`, `theme_id`);

ALTER TABLE `cursus` ADD FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`);

ALTER TABLE `lessons` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`);
