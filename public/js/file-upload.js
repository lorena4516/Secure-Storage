class FileUpload {
    constructor() {
        this.uploadForm = document.getElementById('uploadForm');
        this.fileInput = document.getElementById('fileInput');
        this.uploadProgress = document.getElementById('uploadProgress');
        this.uploadStatus = document.getElementById('uploadStatus');
        this.filesList = document.getElementById('filesList');
        this.storageProgress = document.querySelector('.progress-bar');
        
        this.init();
    }

    init() {
        if (this.uploadForm) {
            this.uploadForm.addEventListener('submit', (e) => this.handleUpload(e));
        }
        
        this.loadUserFiles();
        this.loadStorageInfo();
    }

    handleUpload(e) {
        e.preventDefault();
        
        const file = this.fileInput.files[0];
        if (!file) {
            this.showMessage('Por favor selecciona un archivo', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload');

        this.showProgress(true);

        fetch('../api/files.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            this.showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                this.fileInput.value = '';
                this.loadUserFiles();
                this.loadStorageInfo();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showMessage('Error de conexión: ' + error.message, 'error');
        })
        .finally(() => {
            this.showProgress(false);
        });
    }

    loadStorageInfo() {
       
        fetch('../api/storage.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.updateStorageDisplay(data);

                if (data.percentage > 80) {
                    this.showMessage(
                        `Advertencia: Has usado el ${data.percentage}% de tu almacenamiento`, 
                        'warning'
                    );
                }
            } else {
                console.error('Error del servidor:', data.message);
            }
        })
        .catch(error => {
            console.error('Error al cargar almacenamiento:', error);          
            this.showMessage('No se pudo cargar la información de almacenamiento', 'warning');
        });
    }

    updateStorageDisplay(data) {
        if (this.storageProgress) {
            this.storageProgress.style.width = data.percentage + '%';
            this.storageProgress.setAttribute('aria-valuenow', data.percentage);
            this.storageProgress.textContent = data.percentage + '%';
            
            // Cambiar color según el porcentaje
            if (data.percentage > 80) {
                this.storageProgress.className = 'progress-bar bg-danger';
            } else {
                this.storageProgress.className = 'progress-bar bg-success';
            }
        }     
        const storageElements = document.querySelectorAll('.card-body .d-flex span');
        if (storageElements.length >= 2) {
            storageElements[0].textContent = 'Usado: ' + data.used;
            storageElements[1].textContent = 'Límite: ' + data.limit;
        }
    }

    showProgress(show) {
        if (this.uploadProgress) {
            this.uploadProgress.style.display = show ? 'block' : 'none';
        }
    }

    showMessage(message, type) {
        if (this.uploadStatus) {
            this.uploadStatus.textContent = message;    
            let bootstrapClass;
            switch(type) {
                case 'error':
                    bootstrapClass = 'danger';   
                    break;
                case 'warning':
                    bootstrapClass = 'warning';   
                    break;
                case 'success':
                    bootstrapClass = 'success'; 
                    break;
                case 'info':
                    bootstrapClass = 'info';   
                    break;
                default:
                    bootstrapClass = 'info'; 
            }
            
            this.uploadStatus.className = `alert alert-${bootstrapClass}`;
            this.uploadStatus.style.display = 'block';
            
            setTimeout(() => {
                this.uploadStatus.style.display = 'none';
            }, 5000);
        }
    }

    loadUserFiles() {
        if (!this.filesList) return;

        fetch('../api/files.php?action=list')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red: ' + response.status);
            }
            return response.json();
        })
        .then(files => {
            this.renderFiles(files);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    renderFiles(files) {
        if (!files || files.length === 0) {
            this.filesList.innerHTML = '<tr><td colspan="4" class="text-center">No hay archivos subidos</td></tr>';
            return;
        }

        this.filesList.innerHTML = files.map(file => `
            <tr>
                <td>${this.escapeHtml(file.original_name)}</td>
                <td>${this.formatBytes(file.file_size)}</td>
                <td>${new Date(file.uploaded_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="fileUpload.deleteFile(${file.id})">
                        Eliminar
                    </button>
                </td>
            </tr>
        `).join('');
    }

    deleteFile(fileId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
            return;
        }

        fetch('../api/files.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&file_id=${fileId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const messageType = data.success ? 'success' : 'error';
            this.showMessage(data.message, messageType);
            if (data.success) {
                this.loadUserFiles();
                this.loadStorageInfo();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showMessage('Error de conexión', 'error');
        });
    }

    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.fileUpload = new FileUpload();
});