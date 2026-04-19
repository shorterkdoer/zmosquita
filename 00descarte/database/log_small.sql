

CREATE TABLE `log_small` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stamp` datetime NOT NULL,
  `resumen` text NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

