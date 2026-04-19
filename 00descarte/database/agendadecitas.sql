-- Tabla para agenda de citas
CREATE TABLE IF NOT EXISTS `agendadecitas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `funcionario` bigint(20) unsigned NOT NULL,
  `matriculado` bigint(20) unsigned NOT NULL,
  `motivo` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
