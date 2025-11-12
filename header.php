<?php
// REMOVER session_start() daqui pois já é chamado em cada arquivo
include 'conexao.php';

// Inicializar carrinho_count sempre
$carrinho_count = 0;

// Buscar quantidade de itens no carrinho
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $carrinho_count = count($_SESSION['carrinho']);
}

// Buscar dados do usuário se estiver logado
$usuario = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetShop - Tudo para seu pet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background: #2E7D32;
            color: white;
            padding: 8px 0;
            font-size: 14px;
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-main {
            padding: 15px 0;
        }

        .header-main .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2E7D32;
            text-decoration: none;
        }

        .logo span {
            color: #FF9800;
        }

        .search-bar {
            flex: 1;
            max-width: 600px;
            margin: 0 40px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-bar input:focus {
            border-color: #2E7D32;
        }

        .search-bar button {
            position: absolute;
            right: 5px;
            top: 5px;
            background: #2E7D32;
            border: none;
            border-radius: 20px;
            padding: 7px 15px;
            color: white;
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #666;
            font-size: 12px;
            transition: color 0.3s;
            position: relative;
        }

        .action-item:hover {
            color: #2E7D32;
        }

        .action-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .carrinho-count {
            background: #ff4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            text-align: center;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            min-width: 200px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1000;
        }

        .user-dropdown a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
            color: #2E7D32;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .nav-menu {
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .nav-menu .container {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: #2E7D32;
        }

        .carrinho-item {
            position: relative;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div>Frete grátis acima de R$ 99</div>
                <div>Parcele em até 12x sem juros</div>
            </div>
        </div>

        <div class="header-main">
            <div class="container">
                <a href="index.php" class="logo">Pet<span>Shop</span></a>
                
                <div class="search-bar">
                    <input type="text" placeholder="O que seu pet precisa?">
                    <button><i class="fas fa-search"></i></button>
                </div>

                <div class="header-actions">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="user-menu">
                            <a href="#" class="action-item">
                                <i class="fas fa-user"></i>
                                Minha Conta
                            </a>
                            <div class="user-dropdown">
                                <a href="perfil.php"><i class="fas fa-user-circle"></i> Meu Perfil</a>
                                <a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Meus Pedidos</a>
                                <a href="carrinho.php"><i class="fas fa-shopping-cart"></i> Meu Carrinho</a>
                                <a href="animais.php"><i class="fas fa-paw"></i> Meus Animais</a>
                                <?php if ($_SESSION['usuario_tipo'] == 'admin'): ?>
                                    <a href="admin.php"><i class="fas fa-cogs"></i> Painel Admin</a>
                                <?php endif; ?>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="action-item">
                            <i class="fas fa-user"></i>
                            Entrar
                        </a>
                    <?php endif; ?>

                    <div class="carrinho-item">
                        <a href="carrinho.php" class="action-item">
                            <i class="fas fa-shopping-cart"></i>
                            Carrinho
                            <?php if ($carrinho_count > 0): ?>
                                <span class="carrinho-count"><?php echo $carrinho_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <div class="container">
                <a href="produtos.php?categoria=racao">Ração</a>
                <a href="produtos.php?categoria=brinquedos">Brinquedos</a>
                <a href="produtos.php?categoria=higiene">Higiene</a>
                <a href="produtos.php?categoria=saude">Saúde</a>
                <a href="animais.php">Adoção</a>
            </div>
        </nav>
    </header>