

CREATE TABLE `mailslog` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cuenta` varchar(150) NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
