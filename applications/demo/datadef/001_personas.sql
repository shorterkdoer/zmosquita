CREATE TABLE IF NOT EXISTS demo_personas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(50) NULL,
    email VARCHAR(190) NULL,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);