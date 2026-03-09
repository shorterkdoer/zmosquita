<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = $title ?? 'Convertir PDF a PNG';
$maxFileSize = $maxFileSize ?? '8M';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .upload-area.dragover {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        
        .file-info {
            display: none;
            margin-top: 15px;
        }
        
        .progress {
            display: none;
            margin-top: 15px;
        }
        
        .converted-files {
            display: none;
            margin-top: 20px;
        }
        
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .options-panel {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-file-pdf text-danger"></i> <?= htmlspecialchars($title) ?></h1>
                    <a href="/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Upload Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-upload"></i> Subir Archivo PDF</h5>
                    </div>
                    <div class="card-body">
                        <form id="pdfConverterForm" enctype="multipart/form-data">
                            <!-- Upload Area -->
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Arrastra tu archivo PDF aquí</h5>
                                <p class="text-muted">o haz clic para seleccionar un archivo</p>
                                <input type="file" id="pdfFile" name="pdf_file" accept=".pdf" style="display: none;">
                                <small class="text-muted">Tamaño máximo: <?= htmlspecialchars($maxFileSize) ?></small>
                            </div>

                            <!-- File Info -->
                            <div class="file-info" id="fileInfo">
                                <div class="alert alert-info">
                                    <i class="fas fa-file-pdf"></i>
                                    <span id="fileName"></span>
                                    <span class="badge bg-secondary" id="fileSize"></span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress" id="progressBar">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>

                            <!-- Convert Button -->
                            <div class="text-center mt-3">
                                <button type="submit" id="convertBtn" class="btn btn-primary btn-lg" disabled>
                                    <i class="fas fa-magic"></i> Convertir a PNG
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Converted Files -->
                <div class="converted-files" id="convertedFiles">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-images"></i> Archivos Convertidos</h5>
                        </div>
                        <div class="card-body" id="filesList">
                            <!-- Files will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Options Panel -->
                <div class="options-panel">
                    <h5><i class="fas fa-cog"></i> Opciones de Conversión</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Páginas a convertir:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="convert_pages" id="convertAll" value="1" checked>
                            <label class="form-check-label" for="convertAll">
                                Todas las páginas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="convert_pages" id="convertFirst" value="0">
                            <label class="form-check-label" for="convertFirst">
                                Solo la primera página
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resolution" class="form-label">Resolución (DPI):</label>
                        <select class="form-select" id="resolution" name="resolution">
                            <option value="72">72 DPI (Web)</option>
                            <option value="150" selected>150 DPI (Estándar)</option>
                            <option value="300">300 DPI (Alta calidad)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quality" class="form-label">Calidad:</label>
                        <select class="form-select" id="quality" name="quality">
                            <option value="50">50% (Archivo pequeño)</option>
                            <option value="75">75% (Balanceado)</option>
                            <option value="90" selected>90% (Alta calidad)</option>
                            <option value="100">100% (Máxima calidad)</option>
                        </select>
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success"></i> Formatos soportados: PDF</li>
                            <li><i class="fas fa-check text-success"></i> Salida: PNG de alta calidad</li>
                            <li><i class="fas fa-check text-success"></i> Conversión página por página</li>
                            <li><i class="fas fa-check text-success"></i> Descarga individual o múltiple</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previously Converted Files -->
        <?php if (isset($_SESSION['converted_files']) && !empty($_SESSION['converted_files'])): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Conversión Anterior</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($_SESSION['converted_files'] as $file): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-image fa-2x text-primary mb-2"></i>
                                                <h6>Página <?= $file['page'] ?></h6>
                                                <p class="text-muted small"><?= htmlspecialchars($file['size']) ?></p>
                                                <a href="<?= htmlspecialchars($file['download_url']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i> Descargar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['converted_files']); ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('pdfFile');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const convertBtn = document.getElementById('convertBtn');
            const form = document.getElementById('pdfConverterForm');
            const progressBar = document.getElementById('progressBar');
            const convertedFiles = document.getElementById('convertedFiles');
            const filesList = document.getElementById('filesList');

            // Upload area click handler
            uploadArea.addEventListener('click', () => fileInput.click());

            // Drag and drop handlers
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect();
                }
            });

            // File input change handler
            fileInput.addEventListener('change', handleFileSelect);

            function handleFileSelect() {
                const file = fileInput.files[0];
                if (file) {
                    if (file.type !== 'application/pdf') {
                        alert('Por favor selecciona un archivo PDF válido.');
                        return;
                    }

                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileInfo.style.display = 'block';
                    convertBtn.disabled = false;
                }
            }

            // Form submission handler
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!fileInput.files[0]) {
                    alert('Por favor selecciona un archivo PDF.');
                    return;
                }

                const formData = new FormData();
                formData.append('pdf_file', fileInput.files[0]);
                formData.append('convert_all_pages', document.querySelector('input[name="convert_pages"]:checked').value);
                formData.append('resolution', document.getElementById('resolution').value);
                formData.append('quality', document.getElementById('quality').value);

                // Show progress bar
                progressBar.style.display = 'block';
                convertBtn.disabled = true;
                convertBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Convirtiendo...';

                // Send AJAX request
                fetch('/pdf-converter/convert', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    progressBar.style.display = 'none';
                    convertBtn.disabled = false;
                    convertBtn.innerHTML = '<i class="fas fa-magic"></i> Convertir a PNG';

                    if (data.success) {
                        showConvertedFiles(data.files);
                        showSuccessMessage(data.message);
                        resetForm();
                    } else {
                        showErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    progressBar.style.display = 'none';
                    convertBtn.disabled = false;
                    convertBtn.innerHTML = '<i class="fas fa-magic"></i> Convertir a PNG';
                    showErrorMessage('Error de conexión: ' + error.message);
                });
            });

            function showConvertedFiles(files) {
                filesList.innerHTML = '';
                
                files.forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div>
                            <i class="fas fa-image text-primary"></i>
                            <strong>Página ${file.page}</strong>
                            <span class="text-muted">(${file.size})</span>
                        </div>
                        <a href="${file.download_url}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    `;
                    filesList.appendChild(fileItem);
                });

                convertedFiles.style.display = 'block';
            }

            function showSuccessMessage(message) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(alert, document.querySelector('.row'));
            }

            function showErrorMessage(message) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(alert, document.querySelector('.row'));
            }

            function resetForm() {
                fileInput.value = '';
                fileInfo.style.display = 'none';
                convertBtn.disabled = true;
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
</body>
</html>
