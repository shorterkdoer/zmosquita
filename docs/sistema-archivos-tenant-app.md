# Sistema de Almacenamiento de Files por Tenant/App

Este documento describe el sistema de almacenamiento de archivos con aislación por tenant y aplicación en ZMosquita.

## Arquitectura de Almacenamiento

### Estructura de Directorios

```
storage/
├── .htaccess                        # Protección contra acceso web
├── {tenant_code}/                    # Código del tenant (ej: acme)
│   └── {app_code}/                 # Código de la app (ej: contabilidad)
│       ├── 2026/
│       │   ├── 04/                  # Año/Mes
│       │   │   ├── documents/        # Subdirectorios opcionales
│       │   │   ├── avatars/
│       │   │   ├── uploads/
│       │   │   └── invoices/
│       │   └── 05/
│       ├── 2026/
│       └── ...
├── beta/                             # Otro tenant
│   └── demo/
└── ...
```

**Ejemplos concretos:**
```
storage/acme/contabilidad/2026/04/invoices/factura_123.pdf
storage/acme/contabilidad/2026/04/documents/contrato_456.pdf
storage/acme/sueldos/2026/04/recibos/recibo_789.pdf
storage/beta/demo/2026/04/avatars/user_1_avatar.png
```

### Protección de Seguridad

1. **Directorio `storage/` protegido** con `.htaccess`:
   ```
   Deny from all
   ```

2. **Acceso solo a través de rutas autenticadas** que validan:
   - Tenant del usuario actual
   - Aplicación activa
   - Permisos sobre el archivo

3. **Archivos fuera del webroot** para evitar acceso directo.

## Uso del Sistema de Almacenamiento

### 1. Configuración Inicial

El servicio se registra automáticamente en `bootstrap_core.php`:

```php
// Ya incluido en bootstrap
use ZMosquita\Core\Bootstrap\StorageServiceProvider;
```

### 2. Subir Archivos (en Controlador)

```php
use ZMosquita\Core\Support\Facades\Storage;

class DocumentoController extends BaseController
{
    public function upload(): void
    {
        if (!isset($_FILES['documento'])) {
            // Error
        }

        try {
            // Subir a storage/{tenant}/app/2026/04/documents/
            $result = Storage::upload(
                $_FILES['documento'],
                'documents'  // subpath opcional
            );

            // Guardar referencia en base de datos
            $documentoId = $this->documentoRepo->create([
                'nombre' => $result['original_name'],
                'ruta' => $result['path'],        // acme/contabilidad/2026/04/documentos/...
                'nombre_archivo' => $result['filename'],
                'tamano' => $result['size'],
                'mime' => $result['mime'],
            ]);

            $this->redirect('/documentos');
        } catch (\Throwable $e) {
            // Manejar error
        }
    }
}
```

### 3. Subir Múltiples Archivos

```php
public function uploadMultiple(): void
{
    if (!isset($_FILES['archivos'])) {
        // Error
    }

    $subpath = 'documents/' . date('Y/m');

    foreach ($_FILES['archivos']['name'] as $index => $name) {
        $file = [
            'name' => $name,
            'type' => $_FILES['archivos']['type'][$index],
            'tmp_name' => $_FILES['archivos']['tmp_name'][$index],
            'error' => $_FILES['archivos']['error'][$index],
            'size' => $_FILES['archivos']['size'][$index],
        ];

        $result = Storage::upload($file, $subpath);
        // Guardar en BD...
    }
}
```

### 4. Servir Archivos (Descargas)

```php
public function download(int $documentoId): void
{
    $documento = $this->documentoRepo->find($documentoId);

    if (!$documento) {
        http_response_code(404);
        echo 'Documento no encontrado';
        exit;
    }

    // Verificar acceso
    if (!$this->puedeVerDocumento($documento)) {
        http_response_code(403);
        echo 'Acceso denegado';
        exit;
    }

    // Obtener archivo del storage
    try {
        $contenido = Storage::get(
            $documento['nombre_archivo'],
            dirname($documento['ruta']) // subpath
        );

        // Servir archivo
        header('Content-Type: ' . $documento['mime']);
        header('Content-Disposition: attachment; filename="' . $documento['nombre'] . '"');
        header('Content-Length: ' . strlen($contenido));

        echo $contenido;
        exit;
    } catch (\Throwable $e) {
        http_response_code(404);
        echo 'Archivo no encontrado';
        exit;
    }
}
```

### 5. Generar URLs para Archivos

```php
// La URL es manejada por FileController
public function getFileUrl(string $relativePath): string
{
    return '/file/' . $relativePath;
}

// En vista:
<a href="/file/acme/contabilidad/2026/04/documents/contrato.pdf">Descargar</a>
```

### 6. Eliminar Archivos

```php
public function eliminar(int $documentoId): void
{
    $documento = $this->documentoRepo->find($documentoId);

    // Eliminar archivo físico
    Storage::delete(
        $documento['nombre_archivo'],
        dirname($documento['ruta'])
    );

    // Eliminar registro de BD
    $this->documentoRepo->delete($documentoId);
}
```

## API del Servicio de Almacenamiento

### Storage Facade

```php
use ZMosquita\Core\Support\Facades\Storage;

// Subir archivo
$result = Storage::upload($_FILES['archivo'], 'documents');

// Subir con nombre personalizado
$result = Storage::upload($_FILES['archivo'], 'documents', 'mi_archivo.pdf');

// Subir contenido directamente
$result = Storage::put($contenido, 'contrato.pdf', 'documents');

// Verificar si existe
if (Storage::exists('contrato.pdf', 'documents')) {
    // El archivo existe
}

// Obtener contenido
$contenido = Storage::get('contrato.pdf', 'documents');

// Eliminar
Storage::delete('contrato.pdf', 'documents');

// Listar archivos
$archivos = Storage::list('documents', ['pdf', 'png']);

// Obtener ruta física (para operaciones del sistema)
$path = Storage::path('documents');

// Generar nombre único
$filename = Storage::generateFilename('documento.pdf', 'prefijo_');

// Formatear tamaño
$size = Storage::formatSize(1024000); // "1 MB"
```

### Métodos de FileStorageService

```php
// Subir archivo
$result = $storage->store($file, $subpath, $customFilename);

// Subir contenido
$result = $storage->storeContent($content, $filename, $subpath);

// Obtener archivo
$content = $storage->get($filename, $subpath);

// Verificar existencia
$exists = $storage->exists($filename, $subpath);

// Obtener ruta completa
$fullPath = $storage->getFilePath($filename, $subpath);

// Servir archivo (con headers apropiados)
$storage->output($filename, $subpath, $downloadName);

// Copiar archivo
$storage->copy($oldName, $newName, $fromSubpath, $toSubpath);

// Mover archivo
$storage->move($oldName, $newName, $fromSubpath, $toSubpath);

// Eliminar archivo
$deleted = $storage->delete($filename, $subpath);

// Listar archivos
$files = $storage->listFiles($subpath, $extensions);

// Obtener path relativo para BD
$relativePath = $storage->getRelativePath($subpath, $filename);

// Parsear path relativo a componentes
$parts = $storage->parseRelativePath($relativePath);
// ['tenant' => 'acme', 'app' => 'contabilidad', ...]

// Obtener uso de almacenamiento
$usage = $storage->getUsage($subpath);

// Limpiar archivos viejos (para cron)
$deleted = $storage->cleanupOldFiles(90); // archivos de 90+ días
```

## Configuración

### Variables de Entorno

Agregar a `.env`:

```env
# Storage
STORAGE_PATH=/var/www/zmosquita2/storage
MAX_UPLOAD_SIZE=10M
ALLOWED_FILE_EXTENSIONS=pdf,png,jpg,jpeg,gif,doc,docx,xls,xlsx
```

### Configuración Apache

Asegurar que el directorio `storage/` está fuera del webroot o protegido:

**En VirtualHost:**
```apache
<Directory "/var/www/zmosquita2/storage">
    Require all denied
</Directory>
```

**En .htaccess (ya incluido):**
```
/storage/.htaccess:
    Deny from all
```

### Permisos de Directorios

```bash
# Crear directorio storage con permisos correctos
mkdir -p storage
chmod 775 storage

# Los archivos se crean con 0644 por el servicio
# Los directorios se crean con 0775 por el servicio
```

## Ejemplos de Uso por Caso

### 1. Avatares de Usuario

```php
// Subir avatar
$result = Storage::upload($_FILES['avatar'], 'avatars');

// En BD:
// user.avatar_path = $result['path']
// user.avatar_name = $result['filename']

// En vista:
<img src="/file/<?php echo $user['avatar_path']; ?>" alt="Avatar">
```

### 2. Documentos de Aplicación

```php
// Subir PDF
$subpath = 'documents/' . date('Y/m');
$result = Storage::upload($_FILES['documento'], $subpath);

// En BD:
// documentos.ruta = $result['path']
// documentos.nombre_archivo = $result['filename']
```

### 3. Reportes Generados

```php
// Generar PDF
$pdfContent = $pdf->Output('');

// Guardar en storage
$result = Storage::put($pdfContent, 'reporte_' . $id . '.pdf', 'reports/' . date('Y/m'));

// Guardar referencia
$reporte->ruta_archivo = $result['path'];
```

### 4. Importaciones Masivas

```php
// Subir Excel de importación
$result = Storage::upload($_FILES['importacion'], 'imports/temp');

// Procesar archivo
$excel = $reader->load($result['fullPath']);

// Eliminar después de procesar
Storage::delete($result['filename'], 'imports/temp');
```

## Control de Acceso

### Validación en FileController

El `FileController` valida que:
1. Usuario está autenticado
2. Usuario pertene al tenant del archivo
3. Usuario tiene acceso a la aplicación del archivo
4. El path solicitado no intenta acceder a otro tenant/app

### Ejemplo de Violación de Seguridad

```php
// Usuario del tenant 'acme' intenta acceder a:
// /file/beta/demo/2026/04/documento.pdf

// Resultado: 403 Forbidden
// Porque el usuario no pertene al tenant 'beta'
```

## Mantenimiento

### Limpieza de Archivos Viejos

Crear cron job para limpieza:

```bash
# /etc/cron.monthly/zmosquita-cleanup
php /var/www/zmosquita/bin/cleanup-storage.php
```

**Script de limpieza:**

```php
#!/usr/bin/env php
<?php
require '/var/www/zmosquita/bootstrap_core.php';

use ZMosquita\Core\Storage\FileStorageService;
use ZMosquita\Core\Support\Container;

$storage = Container::instance()->get(FileStorageService::class);

// Limpiar archivos de más de 90 días
$deleted = $storage->cleanupOldFiles(90);

echo "Limpiados {$deleted} archivos antiguos.\n";
```

### Monitoreo de Espacio

```php
$usage = $storage->getUsage();

echo "Archivos: {$usage['count']}\n";
echo "Espacio: {$usage['size_human']}\n";

// Alerta si supera 10GB
if ($usage['size'] > 10 * 1024 * 1024 * 1024) {
    // Enviar alerta
}
```

## Migración desde Sistema Antiguo

Si el sistema usaba `storage/uploads/` con md5:

```php
// Script de migración
$oldFiles = glob('/var/www/zmosquita/storage/uploads/*');

foreach ($oldFiles as $oldPath) {
    // Determinar tenant/app de archivo antiguo
    // Copiar a nueva estructura
    // Actualizar referencias en BD
}
```

## Backup y Restore

### Backup

```bash
# Backup completo de storage
rsync -avz /var/www/zmosquita/storage/ /backup/storage_$(date +%Y%m%d)/

# Backup por tenant
rsync -avz /var/www/zmosquita2/storage/acme/ /backup/acme_storage_$(date +%Y%m%d)/
```

### Restore

```bash
# Restore desde backup
rsync -avz /backup/storage_20250421/ /var/www/zmosquita2/storage/
```

## Troubleshooting

### Error: "No active tenant context"

El contexto del tenant no está establecido. Asegúrate de:

```php
// En middleware o controller
$tenant = Context::currentTenant(); // Debe retornar datos del tenant
```

### Error: "Failed to write file"

Verificar permisos:
```bash
ls -la /var/www/zmosquita2/storage/
# Debe ser 775 para directorios
```

### Archivos no accesibles vía URL

Verificar que:
1. El `.htaccess` en `storage/` existe
2. La ruta `/file/` está configurada en las rutas
3. El middleware de autenticación está activo

## Consideraciones de Producción

1. **Cuotas**: Implementar cuotas por tenant/app
2. **Escaneo**: Implementar escaneo antivirus en uploads
3. **Respaldo**: Backup incremental diario
4. **CDN**: Considerar CDN para archivos servidos frecuentemente
5. **Compresión**: Comprimir PDF/imágenes si es necesario

## Seguridad

- ✅ Validación de MIME type real (no solo extensión)
- ✅ Sanitización de nombres de archivo
- ✅ Protección contra path traversal
- ✅ Aislamiento por tenant/app
- ✅ No ejecución de scripts en storage
- ✅ Control de acceso por permisos
