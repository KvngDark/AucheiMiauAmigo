<?php
include 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Processar adoção
if (isset($_POST['processar_adocao'])) {
    $animal_id = $_POST['animal_id'];
    $tutor_id = $_POST['tutor_id'];
    
    // Buscar animal
    $stmt = $pdo->prepare("SELECT * FROM animais WHERE id = ?");
    $stmt->execute([$animal_id]);
    $animal = $stmt->fetch();
    
    if ($animal && !$animal['adotado']) {
        // Processar adoção
        $sql = "UPDATE animais SET adotado = 1, adotado_por = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tutor_id, $animal_id]);
        
        $sucesso = "Adoção processada com sucesso!";
    } else {
        $erro = "Animal não encontrado ou já adotado.";
    }
}

// Buscar animais disponíveis
$animais_disponiveis = $pdo->query("
    SELECT a.*, t.nome as tipo_nome, u.nome as responsavel 
    FROM animais a 
    LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
    LEFT JOIN usuarios u ON a.usuario_id = u.id 
    WHERE a.adotado = 0 
    ORDER BY a.nome
")->fetchAll();

// Buscar tutores
$tutores = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'tutor' ORDER BY nome")->fetchAll();

// Buscar adoções recentes
$adocoes = $pdo->query("
    SELECT a.*, u.nome as tutor_nome, ur.nome as responsavel_nome, t.nome as tipo_nome
    FROM animais a 
    LEFT JOIN usuarios u ON a.adotado_por = u.id 
    LEFT JOIN usuarios ur ON a.usuario_id = ur.id 
    LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
    WHERE a.adotado = 1 
    ORDER BY a.data_entrada DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processar Adoções - Admin</title>
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

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        select, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            justify-content: center;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
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

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-heart"></i> Processar Adoções</h1>
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
            <a href="admin_usuarios.php"><i class="fas fa-users"></i> Usuários</a>
            <a href="admin_adocoes.php" class="active"><i class="fas fa-heart"></i> Adoções</a>
            <a href="index.php" style="margin-left: auto;"><i class="fas fa-store"></i> Loja</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Processar Adoções</h1>
            <p>Vincule animais a novos tutores</p>
        </div>

        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Adoção -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-paw"></i> Nova Adoção</h2>
            </div>
            <div class="section-content">
                <form method="POST">
                    <div class="form-container">
                        <div class="form-group">
                            <label for="animal_id">Selecionar Animal *</label>
                            <select id="animal_id" name="animal_id" required>
                                <option value="">Selecione um animal...</option>
                                <?php foreach($animais_disponiveis as $animal): ?>
                                    <option value="<?php echo $animal['id']; ?>">
                                        <?php echo $animal['nome']; ?> (<?php echo $animal['tipo_nome']; ?> - <?php echo $animal['responsavel']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tutor_id">Selecionar Tutor *</label>
                            <select id="tutor_id" name="tutor_id" required>
                                <option value="">Selecione um tutor...</option>
                                <?php foreach($tutores as $tutor): ?>
                                    <option value="<?php echo $tutor['id']; ?>">
                                        <?php echo $tutor['nome']; ?> (<?php echo $tutor['email']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="processar_adocao" class="btn btn-success">
                        <i class="fas fa-heart"></i> Processar Adoção
                    </button>
                </form>
            </div>
        </div>

        <!-- Histórico de Adoções -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Histórico de Adoções</h2>
            </div>
            <div class="section-content">
                <?php if (count($adocoes) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Animal</th>
                                <th>Espécie</th>
                                <th>Antigo Responsável</th>
                                <th>Novo Tutor</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($adocoes as $adocao): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $adocao['nome']; ?></strong>
                                    <div style="font-size: 12px; color: #666;">
                                        <?php echo $adocao['raca']; ?> • <?php echo $adocao['idade']; ?> anos
                                    </div>
                                </td>
                                <td><?php echo $adocao['tipo_nome']; ?></td>
                                <td><?php echo $adocao['responsavel_nome']; ?></td>
                                <td><?php echo $adocao['tutor_nome']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($adocao['data_entrada'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">
                        Nenhuma adoção processada ainda.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>