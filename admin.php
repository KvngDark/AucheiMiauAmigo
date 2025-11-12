<?php
include 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Buscar estatísticas
$tutores = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'tutor'")->fetch()['total'];
$ongs = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'ong'")->fetch()['total'];
$animais_total = $pdo->query("SELECT COUNT(*) as total FROM animais")->fetch()['total'];
$animais_adotados = $pdo->query("SELECT COUNT(*) as total FROM animais WHERE adotado = 1")->fetch()['total'];
$animais_disponiveis = $pdo->query("SELECT COUNT(*) as total FROM animais WHERE adotado = 0")->fetch()['total'];
$produtos = $pdo->query("SELECT COUNT(*) as total FROM produtos")->fetch()['total'];

// Buscar últimas adoções
$ultimas_adocoes = $pdo->query("
    SELECT a.nome as animal_nome, a.data_entrada, u.nome as tutor_nome, uad.nome as antigo_tutor
    FROM animais a 
    LEFT JOIN usuarios u ON a.adotado_por = u.id 
    LEFT JOIN usuarios uad ON a.usuario_id = uad.id
    WHERE a.adotado = 1 
    ORDER BY a.data_entrada DESC 
    LIMIT 5
")->fetchAll();

// Buscar produtos com estoque baixo
$estoque_baixo = $pdo->query("
    SELECT * FROM produtos 
    WHERE quantidade_estoque < 10 
    ORDER BY quantidade_estoque ASC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - PetShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: #1a237e;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-nav {
            background: #283593;
            padding: 15px 0;
        }

        .admin-nav .container {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            margin: 30px 0;
        }

        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card.tutores i { color: #2196F3; }
        .stat-card.ongs i { color: #4CAF50; }
        .stat-card.animais i { color: #FF9800; }
        .stat-card.produtos i { color: #9C27B0; }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            display: block;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: #333;
            font-size: 1.3rem;
        }

        .section-content {
            padding: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2196F3;
            color: white;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
        }

        .btn-warning {
            background: #FF9800;
            color: white;
        }

        .quick-actions {
            display: grid;
            gap: 15px;
        }

        .action-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }

        .action-card:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .action-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #2196F3;
        }

        .alert-warning {
            background: #FFF3E0;
            color: #E65100;
            padding: 10px 15px;
            border-radius: 5px;
            border-left: 4px solid #FF9800;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-cogs"></i> Painel Administrativo</h1>
            <div>
                <span>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?></span>
                <a href="logout.php" style="color: white; margin-left: 20px;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="container">
            <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_produtos.php"><i class="fas fa-box"></i> Produtos</a>
            <a href="admin_animais.php"><i class="fas fa-paw"></i> Animais</a>
            <a href="admin_usuarios.php"><i class="fas fa-users"></i> Usuários</a>
            <a href="admin_adocoes.php"><i class="fas fa-heart"></i> Adoções</a>
            <a href="index.php" style="margin-left: auto;"><i class="fas fa-store"></i> Loja</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Visão geral do sistema</p>
        </div>

        <!-- Estatísticas -->
        <div class="dashboard">
            <div class="stat-card tutores">
                <i class="fas fa-user"></i>
                <span class="stat-number"><?php echo $tutores; ?></span>
                <span class="stat-label">Tutores</span>
            </div>
            <div class="stat-card ongs">
                <i class="fas fa-hands-helping"></i>
                <span class="stat-number"><?php echo $ongs; ?></span>
                <span class="stat-label">ONGs</span>
            </div>
            <div class="stat-card animais">
                <i class="fas fa-paw"></i>
                <span class="stat-number"><?php echo $animais_total; ?></span>
                <span class="stat-label">Animais Cadastrados</span>
                <div style="margin-top: 10px; font-size: 12px;">
                    <span style="color: #4CAF50;"><?php echo $animais_adotados; ?> adotados</span> • 
                    <span style="color: #FF9800;"><?php echo $animais_disponiveis; ?> disponíveis</span>
                </div>
            </div>
            <div class="stat-card produtos">
                <i class="fas fa-box"></i>
                <span class="stat-number"><?php echo $produtos; ?></span>
                <span class="stat-label">Produtos</span>
            </div>
        </div>

        <div class="admin-grid">
            <div>
                <!-- Últimas Adoções -->
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-heart"></i> Últimas Adoções</h2>
                    </div>
                    <div class="section-content">
                        <?php if (count($ultimas_adocoes) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Animal</th>
                                        <th>Adotado por</th>
                                        <th>Antigo Tutor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($ultimas_adocoes as $adocao): ?>
                                    <tr>
                                        <td><?php echo $adocao['animal_nome']; ?></td>
                                        <td><?php echo $adocao['tutor_nome']; ?></td>
                                        <td><?php echo $adocao['antigo_tutor']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($adocao['data_entrada'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: #666; padding: 20px;">
                                Nenhuma adoção registrada ainda.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estoque Baixo -->
                <?php if (count($estoque_baixo) > 0): ?>
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-exclamation-triangle"></i> Estoque Baixo</h2>
                    </div>
                    <div class="section-content">
                        <?php foreach($estoque_baixo as $produto): ?>
                            <div class="alert-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong><?php echo $produto['nome']; ?></strong> - 
                                <?php echo $produto['quantidade_estoque']; ?> unidades restantes
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Ações Rápidas -->
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                </div>
                <div class="section-content">
                    <div class="quick-actions">
                        <a href="admin_produtos.php?action=novo" class="action-card">
                            <i class="fas fa-plus"></i>
                            <div>Cadastrar Produto</div>
                        </a>
                        <a href="admin_animais.php" class="action-card">
                            <i class="fas fa-paw"></i>
                            <div>Gerenciar Animais</div>
                        </a>
                        <a href="admin_usuarios.php" class="action-card">
                            <i class="fas fa-users"></i>
                            <div>Ver Usuários</div>
                        </a>
                        <a href="admin_adocoes.php" class="action-card">
                            <i class="fas fa-heart"></i>
                            <div>Processar Adoções</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>