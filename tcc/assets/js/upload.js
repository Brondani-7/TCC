// Upload de fangames
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('gameFile');
    const progressBar = document.getElementById('uploadProgress');

    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/api/upload-game.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fangame enviado com sucesso!');
                    window.location.href = '/pages/game.php?id=' + data.gameId;
                } else {
                    alert('Erro no upload: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro no upload.');
            });
        });
    }

    // Preview de imagem
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Validação de arquivo
function validateFile(file) {
    const maxSize = 100 * 1024 * 1024; // 100MB
    const allowedTypes = ['application/zip', 'application/x-rar-compressed'];
    
    if (file.size > maxSize) {
        alert('Arquivo muito grande. Máximo 100MB.');
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        alert('Formato inválido. Use ZIP ou RAR.');
        return false;
    }
    
    return true;
}