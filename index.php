<?php
include 'conexao.php';

// Buscar produtos em destaque (corrigido)
$produtos_destaque = $pdo->query("SELECT * FROM produtos WHERE destaque = 1 ORDER BY RAND() LIMIT 8")->fetchAll();

// Buscar animais para adoção
$animais_destaque = $pdo->query("
    SELECT a.*, t.nome as tipo_nome 
    FROM animais a 
    LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
    WHERE a.adotado = 0 
    ORDER BY a.data_entrada DESC 
    LIMIT 4
")->fetchAll();
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

        .banner {
            background: linear-gradient(135deg, #2E7D32, #4CAF50);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .banner h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .banner p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn-primary {
            background: #FF9800;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #F57C00;
        }

        .categorias {
            padding: 60px 0;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .categoria-card {
            background: white;
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .categoria-card:hover {
            transform: translateY(-5px);
        }

        .categoria-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            background: #f0f0f0;
        }

        .produtos-destaque, .animais-adocao {
            padding: 60px 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-header h2 {
            font-size: 2rem;
            color: #333;
        }

        .ver-tudo {
            color: #2E7D32;
            text-decoration: none;
            font-weight: 500;
        }

        .produtos-grid, .animais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .produto-card, .animal-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .produto-card:hover, .animal-card:hover {
            transform: translateY(-5px);
        }

        .produto-imagem, .animal-imagem {
            height: 200px;
            overflow: hidden;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .produto-imagem img, .animal-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .produto-info, .animal-info {
            padding: 20px;
        }

        .produto-info h3, .animal-info h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .produto-desc {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .produto-preco {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2E7D32;
            margin-bottom: 15px;
        }

        .btn-adicionar {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-adicionar:hover {
            background: #1B5E20;
        }

        .btn-adotar {
            background: #FF9800;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-adotar:hover {
            background: #F57C00;
        }

        .badge-destaque {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #FF9800;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
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
                            <?php if (isset($_SESSION['carrinho']) && count($_SESSION['carrinho']) > 0): ?>
                                <span class="carrinho-count"><?php echo count($_SESSION['carrinho']); ?></span>
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

    <main>
        <!-- Banner Principal -->
        <section class="banner">
            <div class="container">
                <div class="banner-content">
                    <h1>Tudo que seu pet precisa em um só lugar!</h1>
                    <p>Produtos de qualidade e animais para adoção</p>
                    <a href="produtos.php" class="btn-primary">Comprar Agora</a>
                </div>
            </div>
        </section>

        <!-- Categorias -->
        <section class="categorias">
            <div class="container">
                <h2>Categorias</h2>
                <div class="categorias-grid">
                    <a href="produtos.php?categoria=racao" class="categoria-card">
                        <img src="assets/racao.jpg" alt="Ração" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                        <span style="display: none;">🥩</span>
                        <span>Ração</span>
                    </a>
                    <a href="produtos.php?categoria=brinquedos" class="categoria-card">
                        <img src="assets/brinquedos.jpg" alt="Brinquedos" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                        <span style="display: none;">🎾</span>
                        <span>Brinquedos</span>
                    </a>
                    <a href="produtos.php?categoria=higiene" class="categoria-card">
                        <img src="assets/higiene.jpg" alt="Higiene" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                        <span style="display: none;">🚿</span>
                        <span>Higiene</span>
                    </a>
                    <a href="produtos.php?categoria=saude" class="categoria-card">
                        <img src="assets/saude.jpg" alt="Saúde" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                        <span style="display: none;">💊</span>
                        <span>Saúde</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Produtos em Destaque -->
        <section class="produtos-destaque">
            <div class="container">
                <div class="section-header">
                    <h2>Produtos em Destaque</h2>
                    <a href="produtos.php" class="ver-tudo">Ver tudo</a>
                </div>
                <div class="produtos-grid">
                    <?php foreach($produtos_destaque as $produto): ?>
                    <div class="produto-card">
                        <div class="produto-imagem">
                            <?php if (!empty($produto['imagem'])): ?>
                                <img src="<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
                            <?php else: ?>
                                <span>🛍️</span>
                            <?php endif; ?>
                            <?php if ($produto['destaque']): ?>
                                <span class="badge-destaque">Destaque</span>
                            <?php endif; ?>
                        </div>
                        <div class="produto-info">
                            <h3><?php echo $produto['nome']; ?></h3>
                            <p class="produto-desc"><?php echo substr($produto['descricao'], 0, 60) . '...'; ?></p>
                            <div class="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                            <button class="btn-adicionar" onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Animais para Adoção -->
        <section class="animais-adocao">
            <div class="container">
                <div class="section-header">
                    <h2>Amigos para Adoção</h2>
                    <a href="animais.php" class="ver-tudo">Ver todos</a>
                </div>
                <div class="animais-grid">
                    <?php if (count($animais_destaque) > 0): ?>
                        <?php foreach($animais_destaque as $animal): ?>
                        <div class="animal-card">
                            <div class="animal-imagem">
                                <?php if (!empty($animal['foto'])): ?>
                                    <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                                <?php else: ?>
                                    <span>🐾</span>
                                <?php endif; ?>
                            </div>
                            <div class="animal-info">
                                <h3><?php echo $animal['nome']; ?></h3>
                                <p class="animal-details"><?php echo $animal['tipo_nome']; ?> • <?php echo $animal['idade']; ?> anos</p>
                                <a href="detalhes_animal.php?id=<?php echo $animal['id']; ?>" class="btn-adotar">Conhecer</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <p>Nenhum animal disponível para adoção no momento.</p>
                            <a href="cadastrar_animal.php" class="btn-adotar">Cadastrar Animal</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PetShop</h3>
                    <p>Tudo que seu pet precisa com qualidade e amor.</p>
                </div>
                <div class="footer-section">
                    <h4>Institucional</h4>
                    <a href="#">Sobre nós</a>
                    <a href="#">Nossas lojas</a>
                    <a href="#">Trabalhe conosco</a>
                </div>
                <div class="footer-section">
                    <h4>Ajuda</h4>
                    <a href="#">Fale conosco</a>
                    <a href="#">Dúvidas frequentes</a>
                    <a href="#">Entregas</a>
                </div>
                <div class="footer-section">
                    <h4>Atendimento</h4>
                    <p>📞 (11) 9999-9999</p>
                    <p>✉️ contato@petshop.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 PetShop. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background: #333;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-section h3,
        .footer-section h4 {
            margin-bottom: 15px;
            color: #fff;
        }

        .footer-section a {
            display: block;
            color: #ccc;
            text-decoration: none;
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #4CAF50;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 20px;
            text-align: center;
            color: #ccc;
        }
    </style>

    <script>
    function adicionarAoCarrinho(produtoId) {
        fetch('carrinho.php?adicionar=' + produtoId + '&quantidade=1')
            .then(response => response.text())
            .then(() => {
                // Atualizar contador do carrinho
                location.reload();
            });
    }
    </script>
</body>
</html>