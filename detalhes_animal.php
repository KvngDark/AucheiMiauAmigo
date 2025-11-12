<?php
include 'conexao.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: buscar_animais.php");
    exit;
}

$animal_id = $_GET['id'];

// Buscar dados do animal
$sql = "SELECT a.*, t.nome as tipo_nome, u.* 
        FROM animais a 
        LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
        LEFT JOIN usuarios u ON a.usuario_id = u.id 
        WHERE a.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$animal_id]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: buscar_animais.php");
    exit;
}

// Verificar se o usuário logado é o dono do animal
$is_dono = isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $animal['usuario_id'];
?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $animal['nome']; ?> - Pet Shop</title>
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
            max-width: 1000px;
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
        
        .animal-detalhes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }
        
        .animal-imagem {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .animal-imagem img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .animal-info {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .animal-info h1 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            font-size: 16px;
            margin-top: 5px;
        }
        
        .descricao {
            margin-bottom: 25px;
        }
        
        .descricao h3 {
            margin-bottom: 10px;
            color: #4CAF50;
        }
        
        .responsavel {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .responsavel-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .responsavel-foto {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 3px solid #4CAF50;
        }
        
        .responsavel-info h3 {
            color: #4CAF50;
            margin-bottom: 5px;
        }
        
        .responsavel-tipo {
            color: #666;
            font-size: 14px;
        }
        
        .responsavel-detalhes {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
            text-align: center;
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
        
        .btn-adotar {
            background: #FF9800;
            font-size: 16px;
            font-weight: 600;
            padding: 15px 30px;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-adotar:hover {
            background: #F57C00;
        }
        
        .acoes {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-editar {
            background: #2196F3;
        }
        
        .btn-editar:hover {
            background: #1976D2;
        }
        
        .btn-remover {
            background: #f44336;
        }
        
        .btn-remover:hover {
            background: #d32f2f;
        }
        
        .status-adotado {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
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
                    <li><a href="cadastrar_animal.php">Cadastrar Animal</a></li>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <a href="buscar_animais.php" class="btn btn-voltar">← Voltar para Busca</a>
        
        <div class="animal-detalhes">
            <div class="animal-imagem">
                <?php if ($animal['foto']): ?>
                    <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                <?php else: ?>
                    <div style="height: 400px; display: flex; align-items: center; justify-content: center; color: #999; background: #f0f0f0;">
                        <span>Sem imagem</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="animal-info">
                <h1><?php echo $animal['nome']; ?></h1>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ESPÉCIE</div>
                        <div class="info-value"><?php echo $animal['tipo_nome']; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">RAÇA</div>
                        <div class="info-value"><?php echo $animal['raca'] ?: 'Não informada'; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">IDADE</div>
                        <div class="info-value"><?php echo $animal['idade']; ?> anos</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">SEXO</div>
                        <div class="info-value"><?php echo $animal['sexo'] == 'M' ? 'Macho' : 'Fêmea'; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">DATA DE ENTRADA</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($animal['data_entrada'])); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">STATUS</div>
                        <div class="info-value"><?php echo $animal['adotado'] ? 'Adotado' : 'Disponível para adoção'; ?></div>
                    </div>
                </div>
                
                <div class="descricao">
                    <h3>Sobre <?php echo $animal['nome']; ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($animal['descricao'])); ?></p>
                </div>
                
                <?php if ($animal['adotado']): ?>
                    <div class="status-adotado">
                        🎉 Este animal já foi adotado!
                    </div>
                <?php elseif (!$is_dono && isset($_SESSION['usuario_id'])): ?>
                    <button class="btn btn-adotar" onclick="alert('Entre em contato com o responsável para adotar este animal!')">
                        🏠 Tenho interesse em adotar
                    </button>
                <?php elseif (!$is_dono): ?>
                    <a href="login.php" class="btn btn-adotar">
                        🔐 Faça login para demonstrar interesse
                    </a>
                <?php endif; ?>
                
                <?php if ($is_dono): ?>
                    <div class="acoes">
                        <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-editar">Editar Animal</a>
                        <a href="perfil.php?remover_animal=<?php echo $animal['id']; ?>" 
                           class="btn btn-remover"
                           onclick="return confirm('Tem certeza que deseja remover <?php echo $animal['nome']; ?>?')">
                            Remover Animal
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="responsavel">
                    <div class="responsavel-header">
                        <?php if ($animal['foto_perfil']): ?>
                            <img src="<?php echo $animal['foto_perfil']; ?>" alt="<?php echo $animal['nome']; ?>" class="responsavel-foto">
                        <?php else: ?>
                            <div class="responsavel-foto" style="background: #4CAF50; color: white; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                <?php echo substr($animal['nome'], 0, 1); ?>
                            </div>
                        <?php endif; ?>
                        <div class="responsavel-info">
                            <h3><?php echo $animal['nome']; ?></h3>
                            <div class="responsavel-tipo"><?php echo $animal['tipo'] == 'ong' ? 'ONG' : 'Tutor'; ?></div>
                        </div>
                    </div>
                    
                    <?php if ($animal['telefone']): ?>
                        <div class="responsavel-detalhes">
                            <strong>Telefone:</strong> <?php echo $animal['telefone']; ?><br>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($animal['endereco']): ?>
                        <div class="responsavel-detalhes">
                            <strong>Endereço:</strong> <?php echo $animal['endereco']; ?><br>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($animal['sobre']): ?>
                        <div class="responsavel-detalhes">
                            <strong>Sobre:</strong> <?php echo nl2br(htmlspecialchars($animal['sobre'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>