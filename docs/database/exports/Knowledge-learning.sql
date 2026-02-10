CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `roles` json NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `is_verified` boolean NOT NULL DEFAULT false,
  `activation_token` varchar(255) UNIQUE,
  `activation_token_expires_at` datetime,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `themes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `cursus` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `theme_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price` int NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
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
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `purchases` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int,
  `lesson_id` int,
  `amount` int NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'EUR',
  `status` varchar(255) NOT NULL,
  `stripe_session_id` varchar(255) UNIQUE,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `access_rights` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int,
  `lesson_id` int,
  `granted_at` datetime NOT NULL,
  `purchase_id` int,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `lesson_validations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `cursus_validations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cursus_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE TABLE `certifications` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `theme_id` int NOT NULL,
  `validated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int,
  `updated_by` int
);

CREATE UNIQUE INDEX `users_index_0` ON `users` (`email`);

CREATE UNIQUE INDEX `users_index_1` ON `users` (`activation_token`);

CREATE INDEX `users_index_2` ON `users` (`created_by`);

CREATE INDEX `users_index_3` ON `users` (`updated_by`);

CREATE UNIQUE INDEX `themes_index_4` ON `themes` (`slug`);

CREATE INDEX `themes_index_5` ON `themes` (`created_by`);

CREATE INDEX `themes_index_6` ON `themes` (`updated_by`);

CREATE INDEX `cursus_index_7` ON `cursus` (`theme_id`);

CREATE INDEX `cursus_index_8` ON `cursus` (`created_by`);

CREATE INDEX `cursus_index_9` ON `cursus` (`updated_by`);

CREATE INDEX `lessons_index_10` ON `lessons` (`cursus_id`);

CREATE INDEX `lessons_index_11` ON `lessons` (`position`);

CREATE INDEX `lessons_index_12` ON `lessons` (`created_by`);

CREATE INDEX `lessons_index_13` ON `lessons` (`updated_by`);

CREATE INDEX `purchases_index_14` ON `purchases` (`user_id`);

CREATE INDEX `purchases_index_15` ON `purchases` (`cursus_id`);

CREATE INDEX `purchases_index_16` ON `purchases` (`lesson_id`);

CREATE INDEX `purchases_index_17` ON `purchases` (`status`);

CREATE INDEX `purchases_index_18` ON `purchases` (`created_by`);

CREATE INDEX `purchases_index_19` ON `purchases` (`updated_by`);

CREATE INDEX `access_rights_index_20` ON `access_rights` (`user_id`);

CREATE INDEX `access_rights_index_21` ON `access_rights` (`cursus_id`);

CREATE INDEX `access_rights_index_22` ON `access_rights` (`lesson_id`);

CREATE INDEX `access_rights_index_23` ON `access_rights` (`purchase_id`);

CREATE INDEX `access_rights_index_24` ON `access_rights` (`created_by`);

CREATE INDEX `access_rights_index_25` ON `access_rights` (`updated_by`);

CREATE UNIQUE INDEX `access_rights_index_26` ON `access_rights` (`user_id`, `cursus_id`);

CREATE UNIQUE INDEX `access_rights_index_27` ON `access_rights` (`user_id`, `lesson_id`);

CREATE UNIQUE INDEX `lesson_validations_index_28` ON `lesson_validations` (`user_id`, `lesson_id`);

CREATE INDEX `lesson_validations_index_29` ON `lesson_validations` (`created_by`);

CREATE INDEX `lesson_validations_index_30` ON `lesson_validations` (`updated_by`);

CREATE UNIQUE INDEX `cursus_validations_index_31` ON `cursus_validations` (`user_id`, `cursus_id`);

CREATE INDEX `cursus_validations_index_32` ON `cursus_validations` (`created_by`);

CREATE INDEX `cursus_validations_index_33` ON `cursus_validations` (`updated_by`);

CREATE UNIQUE INDEX `certifications_index_34` ON `certifications` (`user_id`, `theme_id`);

CREATE INDEX `certifications_index_35` ON `certifications` (`created_by`);

CREATE INDEX `certifications_index_36` ON `certifications` (`updated_by`);

ALTER TABLE `users` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `themes` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `themes` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `cursus` ADD FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`);

ALTER TABLE `cursus` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `cursus` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `lessons` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `lessons` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `lessons` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `purchases` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `access_rights` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `lesson_validations` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`cursus_id`) REFERENCES `cursus` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `cursus_validations` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `certifications` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
