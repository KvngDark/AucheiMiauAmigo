<?php
include 'conexao.php';
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar tipos de animais
$tipos_animais = $pdo->query("SELECT * FROM tipos_animais ORDER BY nome")->fetchAll();

// Processar cadastro do animal
if ($_POST && isset($_POST['cadastrar_animal'])) {
    $nome = trim($_POST['nome']);
    $tipo_animal_id = $_POST['tipo_animal_id'];
    $raca = trim($_POST['raca']);
    $idade = $_POST['idade'];
    $sexo = $_POST['sexo'];
    $descricao = trim($_POST['descricao']);
    $usuario_id = $_SESSION['usuario_id'];
    
    // Processar upload da foto do animal
    $foto_animal = null;
    if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImagem($_FILES['foto_animal'], 'animais/');
        if ($upload['success']) {
            $foto_animal = $upload['caminho'];
        } else {
            $erro = $upload['message'];
        }
    } else {
        $erro = "Por favor, selecione uma foto do animal.";
    }
    
    if (!isset($erro)) {
        // Inserir animal
        $sql = "INSERT INTO animais (nome, tipo_animal_id, raca, idade, sexo, descricao, usuario_id, foto, data_entrada) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $tipo_animal_id, $raca, $idade, $sexo, $descricao, $usuario_id, $foto_animal]);
        
        $sucesso = "Animal cadastrado com sucesso!";
        
        // Limpar formulário
        $_POST = array();
    }
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Animal - Pet Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        nav ul li a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .file-input {
            border: 2px dashed #ddd;
            padding: 30px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .file-input:hover {
            border-color: #4CAF50;
        }
        
        .file-input input {
            display: none;
        }
        
        .preview-container {
            margin-top: 15px;
            text-align: center;
        }
        
        .preview-img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2E7D32;
        }
        
        .erro {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
        }
        
        .sucesso {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h1>Cadastrar Animal para Adoção</h1>
        
        <div class="form-container">
            <?php if (isset($erro)): ?>
                <div class="erro"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="sucesso"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="foto_animal">Foto do Animal *</label>
                    <div class="file-input" onclick="document.getElementById('foto_animal').click()">
                        <p>Clique para selecionar uma foto do animal</p>
                        <small>Formatos aceitos: JPG, PNG, GIF (máx. 5MB)</small>
                        <input type="file" id="foto_animal" name="foto_animal" accept="image/*" required onchange="previewImage(this, 'preview-animal')">
                    </div>
                    <div class="preview-container">
                        <img id="preview-animal" class="preview-img" style="display: none;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nome">Nome do Animal *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? $_POST['nome'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tipo_animal_id">Espécie *</label>
                    <select id="tipo_animal_id" name="tipo_animal_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach($tipos_animais as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" <?php echo (isset($_POST['tipo_animal_id']) && $_POST['tipo_animal_id'] == $tipo['id']) ? 'selected' : ''; ?>>
                                <?php echo $tipo['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="raca">Raça</label>
                    <input type="text" id="raca" name="raca" value="<?php echo isset($_POST['raca']) ? $_POST['raca'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="idade">Idade (anos)</label>
                    <input type="number" id="idade" name="idade" min="0" max="50" value="<?php echo isset($_POST['idade']) ? $_POST['idade'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="sexo">Sexo *</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Selecione...</option>
                        <option value="M" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'M') ? 'selected' : ''; ?>>Macho</option>
                        <option value="F" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'F') ? 'selected' : ''; ?>>Fêmea</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição/Características *</label>
                    <textarea id="descricao" name="descricao" required placeholder="Descreva o animal, seu comportamento, necessidades especiais, etc."><?php echo isset($_POST['descricao']) ? $_POST['descricao'] : ''; ?></textarea>
                </div>
                
                <button type="submit" name="cadastrar_animal" class="btn">Cadastrar Animal</button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php include 'footer.php'; ?>