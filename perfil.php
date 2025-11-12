<?php

include 'conexao.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

// Buscar animais do usuário
$animais = $pdo->prepare("SELECT a.*, t.nome as tipo_nome 
                         FROM animais a 
                         LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
                         WHERE a.usuario_id = ? 
                         ORDER BY a.adotado, a.nome");
$animais->execute([$usuario_id]);
$animais = $animais->fetchAll();

// Buscar animais adotados pelo usuário
$animais_adotados = $pdo->prepare("
    SELECT a.*, t.nome as tipo_nome, u.nome as antigo_tutor 
    FROM animais a 
    LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
    LEFT JOIN usuarios u ON a.usuario_id = u.id 
    WHERE a.adotado_por = ? 
    ORDER BY a.nome
");
$animais_adotados->execute([$usuario_id]);
$animais_adotados = $animais_adotados->fetchAll();

// Processar atualização do perfil
if ($_POST && isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $cep = trim($_POST['cep']);
    $logradouro = trim($_POST['logradouro']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $sobre = trim($_POST['sobre']);
    
    // Montar endereço completo
    $endereco = $logradouro . ', ' . $numero;
    if (!empty($complemento)) {
        $endereco .= ' - ' . $complemento;
    }
    $endereco .= ' - CEP: ' . $cep;
    
    // Processar upload da nova foto de perfil (se fornecida)
    $foto_perfil = $usuario['foto_perfil'];
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImagem($_FILES['foto_perfil'], 'perfis/');
        if ($upload['success']) {
            // Remover foto antiga se existir
            if ($foto_perfil && file_exists($foto_perfil)) {
                unlink($foto_perfil);
            }
            $foto_perfil = $upload['caminho'];
        } else {
            $erro = $upload['message'];
        }
    }
    
    if (!isset($erro)) {
        // Atualizar usuário
        $sql = "UPDATE usuarios SET nome = ?, telefone = ?, endereco = ?, sobre = ?, foto_perfil = ?, cep = ?, logradouro = ?, numero = ?, complemento = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $telefone, $endereco, $sobre, $foto_perfil, $cep, $logradouro, $numero, $complemento, $usuario_id]);
        
        $_SESSION['usuario_nome'] = $nome;
        $sucesso = "Perfil atualizado com sucesso!";
        
        // Recarregar dados do usuário
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
    }
}

// Processar alteração de senha
if ($_POST && isset($_POST['alterar_senha'])) {
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    
    // Verificar senha atual
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario_senha = $stmt->fetch()['senha'];
    
    if ($senha_atual !== $usuario_senha) {
        $erro_senha = "Senha atual incorreta.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro_senha = "As novas senhas não coincidem.";
    } elseif (strlen($nova_senha) < 6) {
        $erro_senha = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // Atualizar senha
        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nova_senha, $usuario_id]);
        
        $sucesso_senha = "Senha alterada com sucesso!";
    }
}

// Processar remoção de animal
if (isset($_GET['remover_animal'])) {
    $animal_id = $_GET['remover_animal'];
    
    // Verificar se o animal pertence ao usuário
    $stmt = $pdo->prepare("SELECT foto FROM animais WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$animal_id, $usuario_id]);
    $animal = $stmt->fetch();
    
    if ($animal) {
        // Remover foto do animal se existir
        if ($animal['foto'] && file_exists($animal['foto'])) {
            unlink($animal['foto']);
        }
        
        // Remover animal
        $stmt = $pdo->prepare("DELETE FROM animais WHERE id = ?");
        $stmt->execute([$animal_id]);
        
        header("Location: perfil.php?sucesso=Animal removido com sucesso!");
        exit;
    }
}
?>
<?php include 'header.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Meu Perfil</h1>
            <p>Gerencie suas informações e animais</p>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_GET['sucesso']; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($erro_senha)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro_senha; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($sucesso_senha)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso_senha; ?>
            </div>
        <?php endif; ?>

        <div class="perfil-layout">
            <!-- Sidebar do Perfil -->
            <div class="perfil-sidebar">
                <div class="perfil-card">
                    <div class="perfil-foto-container">
                        <?php if ($usuario['foto_perfil']): ?>
                            <img src="<?php echo $usuario['foto_perfil']; ?>" alt="<?php echo $usuario['nome']; ?>" class="perfil-foto">
                        <?php else: ?>
                            <div class="perfil-foto-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="perfil-status">
                            <span class="status-online"></span>
                            Online
                        </div>
                    </div>
                    
                    <div class="perfil-info">
                        <h2><?php echo $usuario['nome']; ?></h2>
                        <p class="perfil-email"><?php echo $usuario['email']; ?></p>
                        <div class="perfil-badge">
                            <i class="fas fa-paw"></i>
                            <?php echo $usuario['tipo'] == 'ong' ? 'ONG' : 'Tutor'; ?>
                        </div>
                    </div>

                    <div class="perfil-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($animais); ?></span>
                            <span class="stat-label">Animais</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($animais_adotados); ?></span>
                            <span class="stat-label">Adotados</span>
                        </div>
                    </div>

                    <div class="perfil-actions">
                        <a href="cadastrar_animal.php" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i> Cadastrar Animal
                        </a>
                        <a href="animais.php" class="btn btn-outline btn-block">
                            <i class="fas fa-paw"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="perfil-content">
                <!-- Formulário de Edição -->
                <div class="perfil-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-edit"></i> Editar Perfil</h2>
                    </div>
                    <div class="section-content">
                        <form method="POST" enctype="multipart/form-data" class="perfil-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="foto_perfil">Foto de Perfil</label>
                                    <div class="file-upload">
                                        <div class="file-upload-area" onclick="document.getElementById('foto_perfil').click()">
                                            <i class="fas fa-camera"></i>
                                            <span>Clique para alterar a foto</span>
                                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(this, 'preview-perfil')">
                                        </div>
                                        <div class="file-preview">
                                            <?php if ($usuario['foto_perfil']): ?>
                                                <img src="<?php echo $usuario['foto_perfil']; ?>" alt="Preview" id="preview-perfil">
                                            <?php else: ?>
                                                <img id="preview-perfil" style="display: none;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nome">Nome Completo *</label>
                                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telefone">Telefone *</label>
                                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required oninput="formatarTelefone(this)">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cep">CEP *</label>
                                    <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($usuario['cep']); ?>" required onblur="buscarEndereco(this.value)">
                                </div>
                                <div class="form-group">
                                    <label for="logradouro">Logradouro *</label>
                                    <input type="text" id="logradouro" name="logradouro" value="<?php echo htmlspecialchars($usuario['logradouro']); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="numero">Número *</label>
                                    <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($usuario['numero']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="complemento">Complemento</label>
                                    <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($usuario['complemento']); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sobre">Sobre</label>
                                <textarea id="sobre" name="sobre" placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($usuario['sobre']); ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="atualizar_perfil" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Alterações
                                </button>
                                <a href="perfil.php" class="btn btn-outline">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alteração de Senha -->
                <div class="perfil-section">
                    <div class="section-header">
                        <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                    </div>
                    <div class="section-content">
                        <form method="POST" class="perfil-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="senha_atual">Senha Atual</label>
                                    <input type="password" id="senha_atual" name="senha_atual" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nova_senha">Nova Senha</label>
                                    <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                                </div>
                                <div class="form-group">
                                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="alterar_senha" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Alterar Senha
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Animais do Usuário -->
                <div class="perfil-section">
                    <div class="section-header">
                        <h2><i class="fas fa-paw"></i> Meus Animais</h2>
                        <span class="section-badge"><?php echo count($animais); ?></span>
                    </div>
                    <div class="section-content">
                        <?php if (count($animais) > 0): ?>
                            <div class="animais-grid">
                                <?php foreach($animais as $animal): ?>
                                    <div class="animal-card <?php echo $animal['adotado'] ? 'animal-adotado' : ''; ?>">
                                        <div class="animal-imagem">
                                            <?php if ($animal['foto']): ?>
                                                <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                                            <?php else: ?>
                                                <div class="animal-imagem-placeholder">
                                                    <i class="fas fa-paw"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($animal['adotado']): ?>
                                                <div class="animal-status adopted">Adotado</div>
                                            <?php else: ?>
                                                <div class="animal-status available">Disponível</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="animal-info">
                                            <h3><?php echo $animal['nome']; ?></h3>
                                            <p class="animal-type"><?php echo $animal['tipo_nome']; ?> • <?php echo $animal['raca']; ?></p>
                                            <p class="animal-age"><?php echo $animal['idade']; ?> anos • <?php echo $animal['sexo'] == 'M' ? 'Macho' : 'Fêmea'; ?></p>
                                            <div class="animal-actions">
                                                <a href="detalhes_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-sm btn-outline">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-sm btn-outline">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="perfil.php?remover_animal=<?php echo $animal['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Tem certeza que deseja remover <?php echo $animal['nome']; ?>?')">
                                                    <i class="fas fa-trash"></i> Remover
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-paw"></i>
                                <h3>Nenhum animal cadastrado</h3>
                                <p>Comece cadastrando seu primeiro animal para adoção.</p>
                                <a href="cadastrar_animal.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Cadastrar Animal
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Animais Adotados -->
                <div class="perfil-section">
                    <div class="section-header">
                        <h2><i class="fas fa-heart"></i> Animais Adotados</h2>
                        <span class="section-badge"><?php echo count($animais_adotados); ?></span>
                    </div>
                    <div class="section-content">
                        <?php if (count($animais_adotados) > 0): ?>
                            <div class="animais-grid">
                                <?php foreach($animais_adotados as $animal): ?>
                                    <div class="animal-card animal-adotado">
                                        <div class="animal-imagem">
                                            <?php if ($animal['foto']): ?>
                                                <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                                            <?php else: ?>
                                                <div class="animal-imagem-placeholder">
                                                    <i class="fas fa-paw"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="animal-status adopted">Adotado</div>
                                        </div>
                                        <div class="animal-info">
                                            <h3><?php echo $animal['nome']; ?></h3>
                                            <p class="animal-type"><?php echo $animal['tipo_nome']; ?> • <?php echo $animal['raca']; ?></p>
                                            <p class="animal-age"><?php echo $animal['idade']; ?> anos</p>
                                            <p class="animal-responsavel"><small>Antigo responsável: <?php echo $animal['antigo_tutor']; ?></small></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                <h3>Nenhum animal adotado</h3>
                                <p>Visite nossa seção de adoção para encontrar um novo amigo!</p>
                                <a href="animais.php" class="btn btn-primary">
                                    <i class="fas fa-paw"></i> Ver Animais
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .perfil-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
        margin-top: 30px;
    }

    .perfil-sidebar {
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .perfil-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        text-align: center;
    }

    .perfil-foto-container {
        position: relative;
        margin-bottom: 20px;
    }

    .perfil-foto {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #2E7D32;
    }

    .perfil-foto-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2E7D32, #4CAF50);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 4px solid #2E7D32;
    }

    .perfil-foto-placeholder i {
        font-size: 2.5rem;
        color: white;
    }

    .perfil-status {
        position: absolute;
        bottom: 10px;
        right: 20px;
        background: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-online {
        width: 8px;
        height: 8px;
        background: #4CAF50;
        border-radius: 50%;
    }

    .perfil-info h2 {
        color: #333;
        margin-bottom: 5px;
        font-size: 1.4rem;
    }

    .perfil-email {
        color: #666;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .perfil-badge {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .perfil-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin: 25px 0;
        padding: 20px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        display: block;
        font-size: 1.8rem;
        font-weight: bold;
        color: #2E7D32;
    }

    .stat-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
    }

    .perfil-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .perfil-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .section-header {
        background: #f8f9fa;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h2 {
        color: #333;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-badge {
        background: #2E7D32;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }

    .section-content {
        padding: 25px;
    }

    .perfil-form {
        max-width: 100%;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    input, textarea, select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    input:focus, textarea:focus, select:focus {
        border-color: #2E7D32;
        outline: none;
    }

    textarea {
        height: 100px;
        resize: vertical;
    }

    .file-upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s;
    }

    .file-upload-area:hover {
        border-color: #2E7D32;
    }

    .file-upload-area i {
        font-size: 2rem;
        color: #666;
        margin-bottom: 10px;
        display: block;
    }

    .file-preview {
        margin-top: 15px;
        text-align: center;
    }

    .file-preview img {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid #eee;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }

    .btn {
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        justify-content: center;
    }

    .btn-primary {
        background: #2E7D32;
        color: white;
    }

    .btn-primary:hover {
        background: #1B5E20;
    }

    .btn-outline {
        background: white;
        color: #2E7D32;
        border: 1px solid #2E7D32;
    }

    .btn-outline:hover {
        background: #f8f9fa;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 12px;
    }

    .btn-block {
        display: block;
        width: 100%;
    }

    .animais-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .animal-card {
        background: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s;
    }

    .animal-card:hover {
        transform: translateY(-3px);
    }

    .animal-adotado {
        opacity: 0.7;
    }

    .animal-imagem {
        height: 160px;
        position: relative;
        overflow: hidden;
    }

    .animal-imagem img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .animal-imagem-placeholder {
        height: 100%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .animal-imagem-placeholder i {
        font-size: 2rem;
        color: #6c757d;
    }

    .animal-status {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .animal-status.available {
        background: #4CAF50;
        color: white;
    }

    .animal-status.adopted {
        background: #6c757d;
        color: white;
    }

    .animal-info {
        padding: 15px;
    }

    .animal-info h3 {
        color: #333;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }

    .animal-type, .animal-age {
        color: #666;
        font-size: 12px;
        margin-bottom: 3px;
    }

    .animal-actions {
        display: flex;
        gap: 5px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 15px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        border-left: 4px solid #4CAF50;
    }

    .alert-error {
        background: #FFEBEE;
        color: #C62828;
        border-left: 4px solid #F44336;
    }

    @media (max-width: 768px) {
        .perfil-layout {
            grid-template-columns: 1fr;
        }
        
        .perfil-sidebar {
            position: static;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .animais-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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

function formatarTelefone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
    input.value = value;
}

function buscarEndereco(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('logradouro').value = data.logradouro;
                    document.getElementById('complemento').value = data.complemento;
                    document.getElementById('numero').focus();
                }
            })
            .catch(error => console.error('Erro ao buscar CEP:', error));
    }
}
</script>

<?php include 'footer.php'; ?>