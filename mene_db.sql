DROP DATABASE IF EXISTS mene_690972;
CREATE DATABASE mene_690972;
USE mene_690972;

-- Tabella Utenti: Login tramite Email
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL, 
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT 0,
  `no_show_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`), 
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella Servizi
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella Appuntamenti
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  CONSTRAINT `fk_u` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_s` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella Blacklist Orari
CREATE TABLE `blacklist_allowed_slots` (
  `slot_time` time NOT NULL,
  PRIMARY KEY (`slot_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dati Iniziali (Senza Username)
INSERT INTO `users` (`nome`, `cognome`, `email`, `password`, `phone`, `role`) VALUES 
('Admin', 'Principale', 'admin@salone.it', '$2y$10$kS9V1PEq3MTOtePW09naVOVmN5s1vF57racFBDNE.GM1wXK4juyt.', '1111111111', 'admin'),
('Mario', 'Rossi', 'mario@test.it', '$2y$10$kS9V1PEq3MTOtePW09naVOVmN5s1vF57racFBDNE.GM1wXK4juyt.', '3401234567', 'customer');

INSERT INTO `services` (`name`, `duration_minutes`, `price`) VALUES 
('Taglio classico', 30, 20.00), ('Barba', 20, 15.00), ('Colore', 60, 40.00);

INSERT INTO `blacklist_allowed_slots` (`slot_time`) VALUES ('17:00:00'), ('18:00:00');