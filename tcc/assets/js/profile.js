// Sistema de abas do perfil
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.profile-tab');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover active de todas as abas e painéis
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            
            // Adicionar active na aba e painel clicados
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Editar perfil
    const editBtn = document.getElementById('editProfileBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            const newBio = prompt('Digite sua nova biografia:');
            if (newBio) {
                fetch('/api/update-profile.php', {
                    method: 'POST',
                    body: JSON.stringify({ bio: newBio })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Perfil atualizado!');
                        location.reload();
                    }
                });
            }
        });
    }
});

// Upload de avatar
function uploadAvatar(file) {
    if (!file) return;
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    fetch('/api/upload-avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.user-avatar').src = data.avatarUrl;
            alert('Avatar atualizado!');
        }
    });
}

document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    uploadAvatar(e.target.files[0]);
});