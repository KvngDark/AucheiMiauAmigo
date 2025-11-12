<?php
include 'conexao.php';
session_start();

// Filtros de busca
$filtro_especie = isset($_GET['especie']) ? $_GET['especie'] : '';
$filtro_raca = isset($_GET['raca']) ? $_GET['raca'] : '';
$filtro_genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$filtro_ong = isset($_GET['ong']) ? $_GET['ong'] : '';

// Construir consulta com filtros
$sql = "SELECT a.*, t.nome as tipo_nome, u.nome as usuario_nome, u.tipo as usuario_tipo, u.foto_perfil 
        FROM animais a 
        LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
        LEFT JOIN usuarios u ON a.usuario_id = u.id 
        WHERE a.adotado = 0";

$params = [];

if (!empty($filtro_especie)) {
    $sql .= " AND a.tipo_animal_id = ?";
    $params[] = $filtro_especie;
}

if (!empty($filtro_raca)) {
    $sql .= " AND a.raca LIKE ?";
    $params[] = "%$filtro_raca%";
}

if (!empty($filtro_genero)) {
    $sql .= " AND a.sexo = ?";
    $params[] = $filtro_genero;
}

if (!empty($filtro_ong)) {
    $sql .= " AND u.id = ?";
    $params[] = $filtro_ong;
}

$sql .= " ORDER BY a.data_entrada DESC";

// Buscar animais
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll();

// Buscar tipos de animais para o filtro
$tipos_animais = $pdo->query("SELECT * FROM tipos_animais ORDER BY nome")->fetchAll();

// Buscar ONGs para o filtro
$ongs = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'ong' ORDER BY nome")->fetchAll();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Animais - Pet Shop</title>
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
            max-width: 1200px;
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
        
        .filtros {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2E7D32;
        }
        
        .btn-limpar {
            background: #757575;
        }
        
        .btn-limpar:hover {
            background: #424242;
        }
        
        .animais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .animal-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .animal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .animal-imagem {
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            overflow: hidden;
        }
        
        .animal-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .animal-info {
            padding: 20px;
        }
        
        .animal-info h3 {
            margin-bottom: 8px;
            color: #4CAF50;
            font-size: 18px;
        }
        
        .animal-details {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .animal-desc {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .animal-responsavel {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .responsavel-foto {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .responsavel-info {
            flex: 1;
        }
        
        .responsavel-nome {
            font-size: 13px;
            font-weight: 500;
        }
        
        .responsavel-tipo {
            font-size: 11px;
            color: #999;
        }
        
        .sem-resultados {
            text-align: center;
            padding: 40px;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .acoes-filtro {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .contador {
            margin-bottom: 15px;
            color: #666;
            font-style: italic;
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
        <h1>Buscar Animais para Adoção</h1>
        
        <div class="filtros">
            <form method="GET">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label for="especie">Espécie</label>
                        <select id="especie" name="especie">
                            <option value="">Todas as espécies</option>
                            <?php foreach($tipos_animais as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>" <?php echo $filtro_especie == $tipo['id'] ? 'selected' : ''; ?>>
                                    <?php echo $tipo['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="raca">Raça</label>
                        <input type="text" id="raca" name="raca" value="<?php echo htmlspecialchars($filtro_raca); ?>" placeholder="Digite a raça...">
                    </div>
                    
                    <div class="form-group">
                        <label for="genero">Gênero</label>
                        <select id="genero" name="genero">
                            <option value="">Todos</option>
                            <option value="M" <?php echo $filtro_genero == 'M' ? 'selected' : ''; ?>>Macho</option>
                            <option value="F" <?php echo $filtro_genero == 'F' ? 'selected' : ''; ?>>Fêmea</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ong">ONG/Tutor</label>
                        <select id="ong" name="ong">
                            <option value="">Todos</option>
                            <?php foreach($ongs as $ong): ?>
                                <option value="<?php echo $ong['id']; ?>" <?php echo $filtro_ong == $ong['id'] ? 'selected' : ''; ?>>
                                    <?php echo $ong['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="acoes-filtro">
                    <button type="submit" class="btn">Aplicar Filtros</button>
                    <a href="buscar_animais.php" class="btn btn-limpar">Limpar Filtros</a>
                </div>
            </form>
        </div>
        
        <div class="contador">
            <?php echo count($animais); ?> animal(is) encontrado(s)
        </div>
        
        <?php if (count($animais) > 0): ?>
            <div class="animais-grid">
                <?php foreach($animais as $animal): ?>
                <div class="animal-card">
                    <div class="animal-imagem">
                        <?php if ($animal['foto']): ?>
                            <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                        <?php else: ?>
                            <span>Sem imagem</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="animal-info">
                        <h3><?php echo $animal['nome']; ?></h3>
                        <div class="animal-details">
                            <strong>Espécie:</strong> <?php echo $animal['tipo_nome']; ?><br>
                            <strong>Raça:</strong> <?php echo $animal['raca'] ?: 'Não informada'; ?><br>
                            <strong>Idade:</strong> <?php echo $animal['idade']; ?> anos<br>
                            <strong>Sexo:</strong> <?php echo $animal['sexo'] == 'M' ? 'Macho' : 'Fêmea'; ?>
                        </div>
                        <div class="animal-desc">
                            <?php 
                            $descricao = $animal['descricao'];
                            echo strlen($descricao) > 120 ? substr($descricao, 0, 120) . '...' : $descricao; 
                            ?>
                        </div>
                        <a href="detalhes_animal.php?id=<?php echo $animal['id']; ?>" class="btn" style="width: 100%; text-align: center;">Ver Detalhes</a>
                    </div>
                    
                    <div class="animal-responsavel">
                        <?php if ($animal['foto_perfil']): ?>
                            <img src="<?php echo $animal['foto_perfil']; ?>" alt="<?php echo $animal['usuario_nome']; ?>" class="responsavel-foto">
                        <?php else: ?>
                            <div class="responsavel-foto" style="background: #4CAF50; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                <?php echo substr($animal['usuario_nome'], 0, 1); ?>
                            </div>
                        <?php endif; ?>
                        <div class="responsavel-info">
                            <div class="responsavel-nome"><?php echo $animal['usuario_nome']; ?></div>
                            <div class="responsavel-tipo"><?php echo $animal['usuario_tipo'] == 'ong' ? 'ONG' : 'Tutor'; ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="sem-resultados">
                <h3>Nenhum animal encontrado</h3>
                <p>Tente ajustar os filtros de busca ou <a href="cadastrar_animal.php">cadastre um animal</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>