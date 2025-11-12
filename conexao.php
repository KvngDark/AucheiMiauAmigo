<?php
session_start();

$host = 'localhost';
$dbname = 'pet_shop_adocao';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    exit;
}

// Configurações para upload de imagens
$upload_dir = "uploads/";
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Criar diretório de uploads se não existir
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Função para fazer upload de imagem (verificar se já existe)
if (!function_exists('uploadImagem')) {
    function uploadImagem($file, $pasta = '') {
        global $upload_dir, $allowed_types, $max_size;
        
        // Criar subdiretório se não existir
        $pasta_completa = $upload_dir . $pasta;
        if (!file_exists($pasta_completa)) {
            mkdir($pasta_completa, 0777, true);
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no upload: ' . $file['error']];
        }
        
        // Verificar tipo do arquivo
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.'];
        }
        
        // Verificar tamanho
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'Arquivo muito grande. Tamanho máximo: 5MB.'];
        }
        
        // Gerar nome único para o arquivo
        $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . '_' . time() . '.' . $extensao;
        $caminho_completo = $pasta_completa . $nome_arquivo;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
            return ['success' => true, 'caminho' => $caminho_completo];
        } else {
            return ['success' => false, 'message' => 'Erro ao salvar arquivo.'];
        }
    }
}
?>