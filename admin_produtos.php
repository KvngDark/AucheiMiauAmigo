<?php
include 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Processar cadastro/edição de produto
if ($_POST) {
    if (isset($_POST['cadastrar_produto'])) {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $preco = $_POST['preco'];
        $quantidade_estoque = $_POST['quantidade_estoque'];
        $categoria = $_POST['categoria'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImagem($_FILES['imagem'], 'produtos/');
            if ($upload['success']) {
                $imagem = $upload['caminho'];
            }
        }
        
        $sql = "INSERT INTO produtos (nome, descricao, preco, quantidade_estoque, categoria, imagem, destaque) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco, $quantidade_estoque, $categoria, $imagem, $destaque]);
        
        $sucesso = "Produto cadastrado com sucesso!";
    }
    
    if (isset($_POST['editar_produto'])) {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $preco = $_POST['preco'];
        $quantidade_estoque = $_POST['quantidade_estoque'];
        $categoria = $_POST['categoria'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, quantidade_estoque = ?, categoria = ?, destaque = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco, $quantidade_estoque, $categoria, $destaque, $id]);
        
        $sucesso = "Produto atualizado com sucesso!";
    }
}

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
    header("Location: admin_produtos.php?sucesso=Produto excluído com sucesso!");
    exit;
}

// Buscar produtos
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome")->fetchAll();

// Verificar se é para cadastrar novo
$novo_produto = isset($_GET['action']) && $_GET['action'] == 'novo';

$editar_produto = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $editar_produto = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .badge-success {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-warning {
            background: #FFF3E0;
            color: #E65100;
        }

        .badge-danger {
            background: #FFEBEE;
            color: #C62828;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
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
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-box"></i> Gerenciar Produtos</h1>
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
            <a href="admin_produtos.php" class="active"><i class="fas fa-box"></i> Produtos</a>
            <a href="admin_animais.php"><i class="fas fa-paw"></i> Animais</a>
            <a href="admin_usuarios.php"><i class="fas fa-users"></i> Usuários</a>
            <a href="admin_adocoes.php"><i class="fas fa-heart"></i> Adoções</a>
            <a href="index.php" style="margin-left: auto;"><i class="fas fa-store"></i> Loja</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Produtos</h1>
            <a href="admin_produtos.php?action=novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_GET['sucesso']; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Cadastro/Edição -->
        <?php if ($novo_produto || $editar_produto): ?>
        <div class="form-container">
            <h2><?php echo $editar_produto ? 'Editar Produto' : 'Cadastrar Novo Produto'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editar_produto): ?>
                    <input type="hidden" name="id" value="<?php echo $editar_produto['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo $editar_produto ? $editar_produto['nome'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição *</label>
                    <textarea id="descricao" name="descricao" required><?php echo $editar_produto ? $editar_produto['descricao'] : ''; ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="preco">Preço (R$) *</label>
                        <input type="number" id="preco" name="preco" step="0.01" min="0" value="<?php echo $editar_produto ? $editar_produto['preco'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="quantidade_estoque">Estoque *</label>
                        <input type="number" id="quantidade_estoque" name="quantidade_estoque" min="0" value="<?php echo $editar_produto ? $editar_produto['quantidade_estoque'] : '0'; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoria *</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione...</option>
                            <option value="racao" <?php echo ($editar_produto && $editar_produto['categoria'] == 'racao') ? 'selected' : ''; ?>>Ração</option>
                            <option value="brinquedos" <?php echo ($editar_produto && $editar_produto['categoria'] == 'brinquedos') ? 'selected' : ''; ?>>Brinquedos</option>
                            <option value="higiene" <?php echo ($editar_produto && $editar_produto['categoria'] == 'higiene') ? 'selected' : ''; ?>>Higiene</option>
                            <option value="saude" <?php echo ($editar_produto && $editar_produto['categoria'] == 'saude') ? 'selected' : ''; ?>>Saúde</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="destaque" name="destaque" value="1" <?php echo ($editar_produto && $editar_produto['destaque']) ? 'checked' : ''; ?>>
                        <label for="destaque">Produto em destaque</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="<?php echo $editar_produto ? 'editar_produto' : 'cadastrar_produto'; ?>" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $editar_produto ? 'Atualizar' : 'Cadastrar'; ?>
                    </button>
                    <a href="admin_produtos.php" class="btn">Cancelar</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Lista de Produtos -->
        <div style="background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); overflow: hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($produtos) > 0): ?>
                        <?php foreach($produtos as $produto): ?>
                        <tr>
                            <td>
                                <strong><?php echo $produto['nome']; ?></strong>
                                <?php if ($produto['destaque']): ?>
                                    <span class="badge badge-success" style="margin-left: 10px;">Destaque</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $categorias = [
                                    'racao' => 'Ração',
                                    'brinquedos' => 'Brinquedos',
                                    'higiene' => 'Higiene',
                                    'saude' => 'Saúde'
                                ];
                                echo $categorias[$produto['categoria']] ?? $produto['categoria'];
                                ?>
                            </td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if ($produto['quantidade_estoque'] == 0): ?>
                                    <span class="badge badge-danger">Sem estoque</span>
                                <?php elseif ($produto['quantidade_estoque'] < 10): ?>
                                    <span class="badge badge-warning"><?php echo $produto['quantidade_estoque']; ?> unidades</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo $produto['quantidade_estoque']; ?> unidades</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($produto['quantidade_estoque'] > 0): ?>
                                    <span style="color: #4CAF50;">● Ativo</span>
                                <?php else: ?>
                                    <span style="color: #f44336;">● Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="admin_produtos.php?editar=<?php echo $produto['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="admin_produtos.php?excluir=<?php echo $produto['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-box" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #ddd;"></i>
                                Nenhum produto cadastrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>