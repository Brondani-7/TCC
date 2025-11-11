<?php
// Funções utilitárias gerais

// Redirecionamento
function redirect($url) {
    header("Location: $url");
    exit;
}

// Mensagens flash
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Formatação
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Upload de arquivos
function handleFileUpload($file, $allowedTypes, $maxSize, $uploadDir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erro no upload'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Arquivo muito grande'];
    }
    
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de arquivo não permitido'];
    }
    
    $filename = uniqid() . '.' . $fileType;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'error' => 'Erro ao salvar arquivo'];
}
?>