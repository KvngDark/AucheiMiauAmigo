<?php
include 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Buscar usuários (excluindo o admin)
$usuarios = $pdo->query("
    SELECT * FROM usuarios 
    WHERE tipo != 'admin' 
    ORDER BY tipo, nome
")->fetchAll();

// Estatísticas
$tutores = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'tutor'")->fetch()['total'];
$ongs = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'ong'")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

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

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-tutor {
            background: #E3F2FD;
            color: #1976D2;
        }

        .badge-ong {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .user-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .foto-placeholder {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-users"></i> Gerenciar Usuários</h1>
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
            <a href="admin_animais.php"><i class="fas fa-paw"></i> Animais</a>
            <a href="admin_usuarios.php" class="active"><i class="fas fa-users"></i> Usuários</a>
            <a href="admin_adocoes.php"><i class="fas fa-heart"></i> Adoções</a>
            <a href="index.php" style="margin-left: auto;"><i class="fas fa-store"></i> Loja</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Usuários do Sistema</h1>
            <p>Gerencie tutores e ONGs cadastrados</p>
        </div>

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $tutores; ?></span>
                <span class="stat-label">Tutores</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $ongs; ?></span>
                <span class="stat-label">ONGs</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $tutores + $ongs; ?></span>
                <span class="stat-label">Total de Usuários</span>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div style="background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); overflow: hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Data Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($usuario['foto_perfil']): ?>
                                        <img src="<?php echo $usuario['foto_perfil']; ?>" alt="<?php echo $usuario['nome']; ?>" class="user-foto">
                                    <?php else: ?>
                                        <div class="foto-placeholder">
                                            <?php echo substr($usuario['nome'], 0, 1); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo $usuario['nome']; ?></strong>
                                        <?php if ($usuario['sobre']): ?>
                                            <br><small style="color: #666;"><?php echo substr($usuario['sobre'], 0, 50) . '...'; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $usuario['email']; ?></td>
                            <td><?php echo $usuario['telefone']; ?></td>
                            <td>
                                <span class="badge <?php echo $usuario['tipo'] == 'tutor' ? 'badge-tutor' : 'badge-ong'; ?>">
                                    <?php echo $usuario['tipo'] == 'tutor' ? 'Tutor' : 'ONG'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #ddd;"></i>
                                Nenhum usuário cadastrado ainda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>