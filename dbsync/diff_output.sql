-- Script para sincronizar estructura de BD: copro6testing <- copro6

-- Crear tabla agendadecitas
CREATE TABLE `agendadecitas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `funcionario` bigint(20) unsigned NOT NULL,
  `matriculado` bigint(20) unsigned NOT NULL,
  `motivo` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Crear tabla cargos
CREATE TABLE `cargos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `jerarquia` int(11) DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `comprobantespago` MODIFY COLUMN `comprobante` varchar(255) DEFAULT 'NULL';
ALTER TABLE `comprobantespago` MODIFY COLUMN `fecha` date DEFAULT 'NULL';
ALTER TABLE `comprobantespago` MODIFY COLUMN `created_at` datetime NOT NULL DEFAULT 'current_timestamp()';
ALTER TABLE `comprobantespago` MODIFY COLUMN `updated_at` datetime DEFAULT 'NULL';
-- Crear tabla config
CREATE TABLE `config` (
  `id` tinyint(4) NOT NULL,
  `valorunidad` decimal(10,2) NOT NULL,
  `fechaunidad` date NOT NULL,
  `unidadesmes` decimal(10,2) unsigned NOT NULL,
  `unidadesnuevamatric` decimal(10,2) unsigned NOT NULL,
  `alias` varchar(60) DEFAULT NULL,
  `CBU` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Crear tabla debitoconcepto
CREATE TABLE `debitoconcepto` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `concepto` varchar(100) NOT NULL,
  `unidades` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- Crear tabla document_bindings
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

-- Crear tabla document_templates
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

-- Crear tabla document_text_boxes
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

-- Crear tabla estadomatricula
CREATE TABLE `estadomatricula` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `estado` enum('Revision','Verificacion','Listo','Activa','Suspendida','Inhabilitada','Baja') NOT NULL,
  `fecha` date NOT NULL,
  `funcionario_id` bigint(20) unsigned DEFAULT NULL,
  `comision` int(10) unsigned NOT NULL,
  `observaciones` text NOT NULL,
  `fechaintervenido` date NOT NULL,
  `resultado` enum('Aprobado','Rechazado') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Crear tabla estadotramite
CREATE TABLE `estadotramite` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `matriculado` bigint(20) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `texto` text NOT NULL,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fechaintervenido` date DEFAULT NULL,
  `estado` enum('Aprobado','Rechazado') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Crear tabla mailslog
CREATE TABLE `mailslog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(150) NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `matriculas` ADD COLUMN `interviniente` bigint(20) unsigned NOT NULL;
ALTER TABLE `matriculas` ADD COLUMN `revision` date DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `verificado` date DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `comisionotorgante` int(10) unsigned DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `funcionario` bigint(20) unsigned DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `carnet` varchar(60) DEFAULT 'NULL';
ALTER TABLE `matriculas` ADD COLUMN `observaciones` text DEFAULT 'NULL';
-- Crear tabla mediosdepago
CREATE TABLE `mediosdepago` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('Banco','Billetera Virtual','Canal de cobranza','') NOT NULL,
  `Clave` varchar(22) DEFAULT NULL,
  `Alias` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `users` ADD COLUMN `cargo_id` int(11) DEFAULT 'NULL';
