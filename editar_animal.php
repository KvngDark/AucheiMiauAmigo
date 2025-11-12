<?php
include 'conexao.php';
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: perfil.php");
    exit;
}

$animal_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do animal
$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = ? AND usuario_id = ?");
$stmt->execute([$animal_id, $usuario_id]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: perfil.php");
    exit;
}

// Buscar tipos de animais
$tipos_animais = $pdo->query("SELECT * FROM tipos_animais ORDER BY nome")->fetchAll();

// Processar atualização do animal
if ($_POST && isset($_POST['atualizar_animal'])) {
    $nome = trim($_POST['nome']);
    $tipo_animal_id = $_POST['tipo_animal_id'];
    $raca = trim($_POST['raca']);
    $idade = $_POST['idade'];
    $sexo = $_POST['sexo'];
    $descricao = trim($_POST['descricao']);
    $adotado = isset($_POST['adotado']) ? 1 : 0;
    
    // Processar upload da nova foto (se fornecida)
    $foto_animal = $animal['foto'];
    if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImagem($_FILES['foto_animal'], 'animais/');
        if ($upload['success']) {
            // Remover foto antiga se existir
            if ($foto_animal && file_exists($foto_animal)) {
                unlink($foto_animal);
            }
            $foto_animal = $upload['caminho'];
        } else {
            $erro = $upload['message'];
        }
    }
    
    if (!isset($erro)) {
        // Atualizar animal
        $sql = "UPDATE animais SET nome = ?, tipo_animal_id = ?, raca = ?, idade = ?, sexo = ?, descricao = ?, adotado = ?, foto = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $tipo_animal_id, $raca, $idade, $sexo, $descricao, $adotado, $foto_animal, $animal_id]);
        
        $sucesso = "Animal atualizado com sucesso!";
        
        // Recarregar dados do animal
        $stmt = $pdo->prepare("SELECT * FROM animais WHERE id = ?");
        $stmt->execute([$animal_id]);
        $animal = $stmt->fetch();
    }
}
?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Animal - Pet Shop</title>
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2E7D32;
        }
        
        .btn-voltar {
            background: #757575;
            margin-bottom: 20px;
        }
        
        .btn-voltar:hover {
            background: #424242;
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
        
        .acoes {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">Pet Shop & Adoção</div>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="buscar_animais.php">Buscar Animais</a></li>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <a href="detalhes_animal.php?id=<?php echo $animal_id; ?>" class="btn btn-voltar">← Voltar para Detalhes</a>
        
        <h1>Editar Animal</h1>
        
        <div class="form-container">
            <?php if (isset($erro)): ?>
                <div class="erro"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="sucesso"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="foto_animal">Foto do Animal</label>
                    <div class="file-input" onclick="document.getElementById('foto_animal').click()">
                        <p>Clique para alterar a foto do animal</p>
                        <small>Formatos aceitos: JPG, PNG, GIF (máx. 5MB)</small>
                        <input type="file" id="foto_animal" name="foto_animal" accept="image/*" onchange="previewImage(this, 'preview-animal')">
                    </div>
                    <div class="preview-container">
                        <?php if ($animal['foto']): ?>
                            <p>Foto atual:</p>
                            <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>" class="preview-img" id="preview-animal">
                        <?php else: ?>
                            <img id="preview-animal" class="preview-img" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nome">Nome do Animal *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($animal['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tipo_animal_id">Espécie *</label>
                    <select id="tipo_animal_id" name="tipo_animal_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach($tipos_animais as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" <?php echo $animal['tipo_animal_id'] == $tipo['id'] ? 'selected' : ''; ?>>
                                <?php echo $tipo['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="raca">Raça</label>
                    <input type="text" id="raca" name="raca" value="<?php echo htmlspecialchars($animal['raca']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="idade">Idade (anos)</label>
                    <input type="number" id="idade" name="idade" min="0" max="50" value="<?php echo $animal['idade']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="sexo">Sexo *</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Selecione...</option>
                        <option value="M" <?php echo $animal['sexo'] == 'M' ? 'selected' : ''; ?>>Macho</option>
                        <option value="F" <?php echo $animal['sexo'] == 'F' ? 'selected' : ''; ?>>Fêmea</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição/Características *</label>
                    <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($animal['descricao']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="adotado" name="adotado" value="1" <?php echo $animal['adotado'] ? 'checked' : ''; ?>>
                        <label for="adotado">Marcar como adotado</label>
                    </div>
                </div>
                
                <div class="acoes">
                    <button type="submit" name="atualizar_animal" class="btn">Atualizar Animal</button>
                    <a href="detalhes_animal.php?id=<?php echo $animal_id; ?>" class="btn btn-voltar">Cancelar</a>
                </div>
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
            }
        }
    </script>
</body>
</html>