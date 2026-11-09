-- Crear la base de datos si todavia no existe.
-- utf8mb4 permite guardar correctamente caracteres especiales.
CREATE DATABASE IF NOT EXISTS ventas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleccionar la base de datos que utilizara el proyecto.
USE ventas;

-- Crear la tabla categoria si no existe.
-- codigo: identifica cada categoria y no puede repetirse (PRIMARY KEY).
-- nombre: guarda el nombre y es obligatorio (NOT NULL).
CREATE TABLE IF NOT EXISTS categoria (
    codigo INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Insertar cuatro categorias iniciales para practicar GET.
INSERT IGNORE INTO categoria (codigo, nombre) VALUES
    (1, 'Computadoras'),
    (2, 'Telefonos'),
    (3, 'Impresoras'),
    (4, 'Accesorios');


-- Tabla para comprobar los encabezados X-Cedula y X-Contrasena.
-- Ejercicio local: las contrasenas son texto simple (NO usar en produccion).
CREATE TABLE IF NOT EXISTS Programadores (
    Cedula VARCHAR(20) PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Contrasena VARCHAR(20) NOT NULL
);

-- Insertar los programadores para realizar pruebas con Postman.
INSERT INTO Programadores (Cedula, Nombre, Contrasena) VALUES
    ('1', 'Luis', '01'),
    ('2', 'Ana', '02'),
    ('3', 'Maria', '03');
