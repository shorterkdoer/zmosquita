

CREATE TABLE `numeros` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `rotulo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `valor` bigint(20) NOT NULL,
  `key01` varchar(200) NOT NULL,
  `key02` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
