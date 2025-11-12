<?php
include 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Buscar animais
$animais = $pdo->query("
    SELECT a.*, t.nome as tipo_nome, u.nome as usuario_nome, uad.nome as adotado_por_nome
    FROM animais a 
    LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
    LEFT JOIN usuarios u ON a.usuario_id = u.id 
    LEFT JOIN usuarios uad ON a.adotado_por = uad.id
    ORDER BY a.adotado, a.nome
")->fetchAll();

// Buscar tipos de animais
$tipos_animais = $pdo->query("SELECT * FROM tipos_animais ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Animais - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Usar o mesmo estilo do admin.php */
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

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
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

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-success {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-secondary {
            background: #E0E0E0;
            color: #424242;
        }

        .animal-foto {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
        }

        .foto-placeholder {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-paw"></i> Gerenciar Animais</h1>
            <div>
                <span>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?></span>
                <a href="admin.php" style="color: white; margin-left: 20px;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="container">
            <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_produtos.php"><i class="fas fa-box"></i> Produtos</a>
            <a href="admin_animais.php" class="active"><i class="fas fa-paw"></i> Animais</a>
            <a href="admin_usuarios.php"><i class="fas fa-users"></i> Usuários</a>
            <a href="admin_adocoes.php"><i class="fas fa-heart"></i> Adoções</a>
            <a href="index.php" style="margin-left: auto;"><i class="fas fa-store"></i> Loja</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Animais Cadastrados</h1>
            <p>Gerencie todos os animais do sistema</p>
        </div>

        <!-- Lista de Animais -->
        <div style="background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); overflow: hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>Espécie</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Adotado por</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($animais) > 0): ?>
                        <?php foreach($animais as $animal): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($animal['foto']): ?>
                                        <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>" class="animal-foto">
                                    <?php else: ?>
                                        <div class="foto-placeholder">
                                            <i class="fas fa-paw"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo $animal['nome']; ?></strong>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php echo $animal['raca']; ?> • <?php echo $animal['idade']; ?> anos
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $animal['tipo_nome']; ?></td>
                            <td><?php echo $animal['usuario_nome']; ?></td>
                            <td>
                                <?php if ($animal['adotado']): ?>
                                    <span class="badge badge-success">Adotado</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Disponível</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($animal['adotado_por_nome']): ?>
                                    <?php echo $animal['adotado_por_nome']; ?>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($animal['data_entrada'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-paw" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #ddd;"></i>
                                Nenhum animal cadastrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>