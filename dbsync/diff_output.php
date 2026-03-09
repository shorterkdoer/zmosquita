<?php
// Script para sincronizar estructura de BD

/* Crear tabla anuncios */
CREATE TABLE `anuncios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(120) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `imagendestacada` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `fecha` date DEFAULT current_timestamp(),
  `texto` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla cargos */
CREATE TABLE `cargos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `jerarquia` int(11) DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla ciudad */
CREATE TABLE `ciudad` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Crear tabla comision */
CREATE TABLE `comision` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_presi` bigint(20) unsigned NOT NULL,
  `user_vice` bigint(20) unsigned NOT NULL,
  `user_secre` bigint(20) unsigned NOT NULL,
  `inicio` date NOT NULL,
  `fin` date NOT NULL,
  `carnet_presi` varchar(60) NOT NULL,
  `carnet_vice` varchar(60) NOT NULL,
  `activa` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla comprobantespago */
CREATE TABLE `comprobantespago` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `comprobante` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `observaciones` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `tramite_id` bigint(20) unsigned zerofill DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla config */
CREATE TABLE `config` (
  `id` tinyint(4) NOT NULL,
  `valorunidad` decimal(10,2) NOT NULL,
  `fechaunidad` date NOT NULL,
  `unidadesmes` decimal(10,2) unsigned NOT NULL,
  `unidadesnuevamatric` decimal(10,2) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla datospersonales */
CREATE TABLE `datospersonales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `ciudad_id` bigint(20) unsigned DEFAULT NULL,
  `provincia_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_calle` varchar(100) DEFAULT NULL,
  `direccion_numero` varchar(10) DEFAULT NULL,
  `direccion_piso` varchar(10) DEFAULT NULL,
  `direccion_depto` varchar(10) DEFAULT NULL,
  `direccion_cp` varchar(10) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `mailparticular` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `maillaboral` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `datospersonales_user_id_foreign` (`user_id`) USING BTREE,
  KEY `datospersonales_ciudad_id_foreign` (`ciudad_id`),
  KEY `datospersonales_provincia_id_foreign` (`provincia_id`),
  KEY `mailparticular` (`mailparticular`),
  KEY `maillaboral` (`maillaboral`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Crear tabla debitoconcepto */
CREATE TABLE `debitoconcepto` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `concepto` varchar(100) NOT NULL,
  `unidades` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

/* Crear tabla document_bindings */
CREATE TABLE `document_bindings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL,
  `variable_name` varchar(255) DEFAULT NULL,
  `table_name` varchar(255) DEFAULT NULL,
  `column_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `document_bindings_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `document_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla document_templates */
CREATE TABLE `document_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `page_size` enum('A4','Letter','Legal','Custom') DEFAULT 'A4',
  `orientation` enum('portrait','landscape') DEFAULT 'portrait',
  `width_mm` int(11) DEFAULT NULL,
  `height_mm` int(11) DEFAULT NULL,
  `background_image_path` varchar(255) DEFAULT NULL,
  `watermark_text` varchar(255) DEFAULT NULL,
  `watermark_opacity` float DEFAULT NULL,
  `qr_code_position` varchar(50) DEFAULT NULL,
  `qr_code_x_mm` int(11) DEFAULT NULL,
  `qr_code_y_mm` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla document_text_boxes */
CREATE TABLE `document_text_boxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `variable_name` varchar(255) DEFAULT NULL,
  `x_mm` int(11) DEFAULT NULL,
  `y_mm` int(11) DEFAULT NULL,
  `width_mm` int(11) DEFAULT NULL,
  `height_mm` int(11) DEFAULT NULL,
  `font_size` int(11) DEFAULT NULL,
  `font_family` varchar(50) DEFAULT NULL,
  `alignment` enum('left','center','right') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `document_text_boxes_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `document_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla log_small */
CREATE TABLE `log_small` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stamp` datetime NOT NULL,
  `resumen` text NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla mailslog */
CREATE TABLE `mailslog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(150) NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `matriculas` ADD COLUMN `comisionotorgante` int(10) unsigned DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `funcionario` bigint(20) unsigned DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `carnet` varchar(60) DEFAULT 'NULL';
/* Crear tabla mediosdepago */
CREATE TABLE `mediosdepago` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('Banco','Billetera Virtual','Canal de cobranza','') NOT NULL,
  `Clave` varchar(22) DEFAULT NULL,
  `Alias` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla numeros */
CREATE TABLE `numeros` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rotulo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `valor` bigint(20) NOT NULL,
  `key01` varchar(200) NOT NULL,
  `key02` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rotulo` (`rotulo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla provincias */
CREATE TABLE `provincias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Crear tabla tramites */
CREATE TABLE `tramites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/* Crear tabla users */
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `activation_token` varchar(255) DEFAULT NULL,
  `mailed` date DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `totp_secret` varchar(32) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_cargo` (`cargo_id`),
  CONSTRAINT `fk_users_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

