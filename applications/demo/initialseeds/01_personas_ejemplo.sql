-- Initial seeds for demo app
-- This file is executed after schema installation

INSERT INTO demo_personas (nombre, apellido, email, telefono, created_at, updated_at) VALUES
('Juan', 'Pérez', 'juan.perez@example.com', '+54 11 1234-5678', NOW(), NOW()),
('María', 'García', 'maria.garcia@example.com', '+54 11 2345-6789', NOW(), NOW()),
('Carlos', 'López', 'carlos.lopez@example.com', '+54 11 3456-7890', NOW(), NOW());
