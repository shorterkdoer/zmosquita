

CREATE TABLE `datospersonales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ciudad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provincia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direccion_calle` varchar(100) DEFAULT NULL,
  `direccion_numero` varchar(10) DEFAULT NULL,
  `direccion_piso` varchar(10) DEFAULT NULL,
  `direccion_depto` varchar(10) DEFAULT NULL,
  `direccion_cp` varchar(10) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

