# Deployment de ZMosquita en Servidor Ubuntu

Guía completa para desplegar ZMosquita en un servidor Ubuntu en producción con Apache, MariaDB y PHP.

## Requisitos Previos

### Sistema Operativo
- Ubuntu Server 20.04 LTS o superior
- Acceso con privilegios sudo
- Mínimo 2GB RAM (4GB recomendado)
- 20GB espacio en disco

### Stack Tecnológico
- Apache 2.4+
- MariaDB 10.6+ o MySQL 8.0+
- PHP 8.1 o superior

### Accesos Necesarios
- Acceso SSH al servidor
- Acceso a la base de datos como administrador
- Acceso a repositorio Git (GitHub)
- Dominio o subdominio configurado (DNS)

## Paso 1: Verificar/Instalar Prerrequisitos

### 1.1 Verificar versión de PHP

```bash
php -v
```

**Versión mínima requerida:** PHP 8.1

Si necesitas instalar o actualizar PHP:

```bash
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.4 php8.4-fpm php8.4-mysql php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-gd php8.4-intl
```

### 1.2 Verificar/Instalar Apache

```bash
apache2 -v
```

Si no está instalado:

```bash
sudo apt update
sudo apt install apache2 libapache2-mod-php8.4
```

### 1.3 Verificar/Instalar MariaDB

```bash
mariadb --version
# o
mysql --version
```

Si no está instalado:

```bash
sudo apt update
sudo apt install mariadb-server mariadb-client
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation
```

### 1.4 Instalar extensiones PHP adicionales

```bash
sudo apt install php8.4-bcmath php8.4-imagick composer git unzip
```

### 1.5 Habilitar módulos de Apache

```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

## Paso 2: Preparar Base de Datos

### 2.1 Crear base de datos IAM

```bash
sudo mysql -u root -p
```

```sql
-- Crear base de datos principal
CREATE DATABASE zmosquita_iam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario dedicado
CREATE USER 'zmosquita_user'@'localhost' IDENTIFIED BY 'contraseña_segura_aleatoria';

-- Otorgar privilegios
GRANT ALL PRIVILEGES ON zmosquita_iam.* TO 'zmosquita_user'@'localhost';
GRANT CREATE, ALTER, DROP, INSERT, UPDATE, DELETE, SELECT, INDEX, LOCK TABLES ON *.* TO 'zmosquita_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Salir
EXIT;
```

### 2.2 Verificar conexión

```bash
mysql -u zmosquita_user -p -e "SHOW DATABASES;"
```

## Paso 3: Obtener Código desde GitHub

### 3.1 Clonar repositorio

```bash
# Opción A: Clonar en /var/www (recomendado)
cd /var/www
sudo git clone https://github.com/tu-organizacion/zmosquita.git zmosquita

# Opción B: Si el repositorio es privado
cd /var/www
sudo git clone https://github.com/tu-organizacion/zmosquita.git zmosquita
# Luego configurar credenciales o usar SSH
```

### 3.2 Configurar permisos

```bash
# Propietario: www-data (usuario de Apache)
sudo chown -R www-data:www-data /var/www/zmosquita

# Permisos de directorios (755)
sudo find /var/www/zmosquita -type d -exec chmod 755 {} \;

# Permisos de archivos (644)
sudo find /var/www/zmosquita -type f -exec chmod 644 {} \;

# Directorios que necesitan escritura
sudo chmod -R 775 /var/www/zmosquita/storage
sudo chmod -R 775 /var/www/zmosquita/tmp
sudo chmod -R 775 /var/www/zmosquita/.claude

# Archivo .env
sudo chmod 600 /var/www/zmosquita/.env
```

### 3.3 Instalar dependencias de Composer

```bash
cd /var/www/zmosquita
sudo -u www-data composer install --no-dev --optimize-autoloader
```

**Nota:** Si no existe el archivo `composer.lock`:

```bash
sudo -u www-data composer update
sudo -u www-data composer install --no-dev
```

## Paso 4: Configurar Entorno

### 4.1 Crear archivo .env

```bash
cd /var/www/zmosquita
sudo cp .env.example .env
sudo nano .env
```

### 4.2 Configurar variables de entorno

```env
# Application
APP_NAME=ZMosquita
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=zmosquita_iam
DB_USER=zmosquita_user
DB_PASSWORD=contraseña_segura_aleatoria
DB_CHARSET=utf8mb4
DB_COLLATE=utf8mb4_unicode_ci

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=tu.smtp.com
MAIL_PORT=465
MAIL_USERNAME=noreply@tu-dominio.com
MAIL_PASSWORD=tu_contraseña_email
MAIL_ENCRYPTION=smtps
MAIL_FROM_ADDRESS=noreply@tu-dominio.com
MAIL_FROM_NAME=ZMosquita

# reCAPTCHA (opcional, si se usa)
RECAPTCHA_SITE_KEY=tu_site_key
RECAPTCHA_SECRET_KEY=tu_secret_key

# Application Secret Key
# Generar con: openssl rand -base64 32
APP_KEY=tu_clave_generada_openssl
```

### 4.3 Generar APP_KEY

```bash
openssl rand -base64 32
```

Copiar el resultado y pegarlo en `APP_KEY` en el archivo `.env`.

## Paso 5: Instalar Esquema Core

### 5.1 Ejecutar instalación del core

```bash
cd /var/www/zmosquita
sudo -u www-data php bin/zmosquita install:core
```

**Salida esperada:**
```
Core instalado correctamente.
 - /var/www/zmosquita/core/datadef/01_users.sql
 - /var/www/zmosquita/core/datadef/02_tentants.sql
 - /var/www/zmosquita/core/datadef/03_applications.sql
 ...
```

### 5.2 Verificar instalación en base de datos

```bash
mysql -u zmosquita_user -p zmosquita_iam -e "SHOW TABLES;"
```

**Deberías ver tablas como:**
- `iam_users`
- `iam_tenants`
- `iam_applications`
- `iam_permissions`
- `iam_roles`
- etc.

## Paso 6: Configurar Apache VirtualHost

### 6.1 Crear archivo de configuración

```bash
sudo nano /etc/apache2/sites-available/zmosquita.conf
```

### 6.2 Contenido del VirtualHost

**Para HTTP (para pruebas iniciales):**

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    ServerAdmin admin@tu-dominio.com

    DocumentRoot /var/www/zmosquita

    <Directory /var/www/zmosquita>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Proteger archivos sensibles
        <FilesMatch "^\.">
            Require all denied
        </FilesMatch>

        <FilesMatch "(env\.json|\.env|composer\.(json|lock))">
            Require all denied
        </FilesMatch>
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/zmosquita_error.log
    CustomLog ${APACHE_LOG_DIR}/zmosquita_access.log combined

    # Redirigir a HTTPS (después de configurar SSL)
    # Redirect permanent / https://tu-dominio.com/
</VirtualHost>
```

**Para HTTPS con Let's Encrypt:**

```apache
<VirtualHost *:443>
    ServerName tu-dominio.com
    ServerAdmin admin@tu-dominio.com

    DocumentRoot /var/www/zmosquita

    <Directory /var/www/zmosquita>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Proteger archivos sensibles
        <FilesMatch "^\.">
            Require all denied
        </FilesMatch>

        <FilesMatch "(env\.json|\.env|composer\.(json|lock))">
            Require all denied
        </FilesMatch>
    </Directory>

    # SSL
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/tu-dominio.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/tu-dominio.com/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    # HSTS
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/zmosquita_error.log
    CustomLog ${APACHE_LOG_DIR}/zmosquita_access.log combined
</VirtualHost>
```

### 6.3 Habilitar sitio y reiniciar Apache

```bash
sudo a2ensite zmosquita.conf
sudo systemctl reload apache2
```

## Paso 7: Configurar SSL con Let's Encrypt (Recomendado)

### 7.1 Instalar Certbot

```bash
sudo apt install certbot python3-certbot-apache
```

### 7.2 Obtener certificado

```bash
sudo certbot --apache -d tu-dominio.com -d www.tu-dominio.com
```

Seguir las instrucciones del asistente.

### 7.3 Configurar renovación automática

```bash
sudo crontab -e
```

Agregar:
```
0 0 * * * certbot renew --quiet
```

## Paso 8: Verificación y Testing

### 8.1 Verificar que el sitio responde

```bash
curl -I http://tu-dominio.com
# o
curl -I https://tu-dominio.com
```

**Deberías ver:**
```
HTTP/1.1 200 OK
```

### 8.2 Verificar logs de Apache

```bash
sudo tail -f /var/log/apache2/zmosquita_error.log
```

### 8.3 Crear usuario administrador inicial

```bash
mysql -u zmosquita_user -p zmosquita_iam
```

```sql
-- Crear usuario admin
INSERT INTO iam_users (
    email,
    password_hash,
    name,
    status,
    created_at,
    updated_at
) VALUES (
    'admin@tu-dominio.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password
    'Administrador',
    'active',
    NOW(),
    NOW()
);
```

**Nota:** El hash anterior corresponde al password `password`. Debes cambiarlo inmediatamente.

Para generar un hash seguro:

```php
<?php
echo password_hash('tu_contraseña_segura', PASSWORD_BCRYPT);
?>
```

### 8.4 Acceder a la aplicación

1. Abrir navegador: `https://tu-dominio.com`
2. Iniciar sesión con:
   - Email: `admin@tu-dominio.com`
   - Password: `password` (cambiar inmediatamente)

## Paso 9: Post-Instalación

### 9.1 Configurar backup automatizado

Crear script `/usr/local/bin/backup-zmosquita.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups/zmosquita"
DATE=$(date +%Y%m%d_%H%M%S)
DB_USER="zmosquita_user"
DB_PASS="contraseña_segura_aleatoria"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Backup de base de datos IAM
mysqldump -u $DB_USER -p$DB_PASS zmosquita_iam | gzip > $BACKUP_DIR/iam_$DATE.sql.gz

# Backup de catálogos de tenants
for catalog in $(mysql -u $DB_USER -p$DB_PASS -N -s -e "SELECT catalog FROM zmosquita_iam.iam_tenants WHERE deleted_at IS NULL"); do
    mysqldump -u $DB_USER -p$DB_PASS $catalog | gzip > $BACKUP_DIR/${catalog}_$DATE.sql.gz
done

# Backup de archivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/zmosquita

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completado: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/backup-zmosquita.sh
```

Configurar cron:

```bash
sudo crontab -e
```

Agregar backup diario a las 2 AM:
```
0 2 * * * /usr/local/bin/backup-zmosquita.sh >> /var/log/zmosquita-backup.log 2>&1
```

### 9.2 Configurar monitoreo

Instalar herramientas de monitoreo básicas:

```bash
sudo apt install htop iotop nethogs
```

### 9.3 Configurar firewall

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### 9.4 Afinar PHP para producción

Editar `/etc/php/8.4/apache2/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_vars = 5000
date.timezone = America/Argentina/Buenos_Aires
```

Reiniciar Apache:

```bash
sudo systemctl restart apache2
```

## Paso 10: Crear Primer Tenant

```bash
cd /var/www/zmosquita
sudo -u www-data php bin/zmosquita tenant:make cliente1 "Cliente Demo" cliente1_db
```

Verificar:

```bash
sudo -u www-data php bin/zmosquita tenant:list
```

## Troubleshooting

### Error: "500 Internal Server Error"

Verificar logs:
```bash
sudo tail -f /var/log/apache2/zmosquita_error.log
```

Causas comunes:
1. Permisos incorrectos en archivos
2. `.env` no configurado
3. Dependencias de Composer no instaladas

### Error: "Database connection failed"

Verificar:
1. Credenciales en `.env`
2. Servicio MariaDB corriendo: `sudo systemctl status mariadb`
3. Usuario tiene permisos: `mysql -u zmosquita_user -p`

### Error: "Permission denied"

Verificar permisos:
```bash
sudo chown -R www-data:www-data /var/www/zmosquita
sudo chmod -R 755 /var/www/zmosquita
```

### Error: "Site not accessible"

1. Verificar que Apache esté corriendo: `sudo systemctl status apache2`
2. Verificar VirtualHost habilitado: `sudo a2ensite zmosquita`
3. Verificar puerto 80/443 abierto: `sudo ufw status`

## Actualización del Sistema

### Actualizar código desde GitHub

```bash
cd /var/www/zmosquita
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev
```

### Actualizar base de datos (si hay cambios)

```bash
sudo -u www-data php bin/zmosquita install:core
```

## Checklist de Producción

- [ ] PHP 8.1+ instalado y configurado
- [ ] Apache 2.4+ instalado y configurado
- [ ] MariaDB 10.6+ instalado y configurado
- [ ] Base de datos IAM creada
- [ ] Usuario de base de datos con permisos
- [ ] Código clonado desde GitHub
- [ ] Dependencias de Composer instaladas
- [ ] Archivo `.env` configurado
- [ ] Esquema core instalado
- [ ] VirtualHost de Apache configurado
- [ ] SSL/TLS configurado con Let's Encrypt
- [ ] Firewall configurado
- [ ] Backup automatizado configurado
- [ ] Usuario administrador creado
- [ ] Primer tenant creado
- [ ] Aplicación accesible vía HTTPS
- [ ] Contraseña de admin cambiada

## Recursos Adicionales

- [Documentación de tenants](./deployment-tenant.md)
- [Documentación de aplicaciones](./publicacion-aplicacion.md)
- [Documentación de deployment de apps](./deployment-aplicacion-tenant.md)
