
CREATE TABLE `matriculas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notaddjj` varchar(255) DEFAULT NULL COMMENT 'Nota declaración jurada',
  `dnifrente` varchar(255) DEFAULT NULL COMMENT 'DNI frente',
  `dnidorso` varchar(255) DEFAULT NULL COMMENT 'DNI dorso',
  `titulooriginalfrente` varchar(255) DEFAULT NULL COMMENT 'Título original frente',
  `titulooriginaldorso` varchar(255) DEFAULT NULL COMMENT 'Título original dorso',
  `fotoregistrodegraduados` varchar(255) DEFAULT NULL COMMENT 'Certificado Analítico',
  `fotocarnet` varchar(255) DEFAULT NULL COMMENT 'Foto carnet',
  `antecedentespenales` varchar(255) DEFAULT NULL COMMENT 'Certificado de antecedentes penales',
  `libredeudaalimentario` varchar(255) DEFAULT NULL COMMENT 'Libre deuda alimentos',
  `constanciaCUIL` varchar(255) DEFAULT NULL COMMENT 'Constancia de CUIL',
  `apostillado` varchar(255) DEFAULT NULL COMMENT 'Apostillado',
  `matriculaprevia` varchar(255) DEFAULT NULL COMMENT 'Matrícula previa',
  `matriculaministerio` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `matriculaasignada` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `certificadoetica` varchar(255) DEFAULT NULL COMMENT 'Certificado de etica',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `intervenido` datetime DEFAULT NULL COMMENT 'Fecha de intervención',
  `aprobado` datetime DEFAULT NULL COMMENT 'Fecha de aprobación',
  `baja` datetime DEFAULT NULL COMMENT 'Fecha de baja',
  `estado` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

