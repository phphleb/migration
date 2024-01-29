/* If the table is not created automatically, then run this code. */
/* MySQL/MariaDB/PostgreSQL */

CREATE TABLE IF NOT EXISTS migrations (
    label bigint NOT NULL,
    datecreate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;