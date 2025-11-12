<?php
session_start();
include 'conexao.php';

// Processar ações do carrinho
if (isset($_GET['adicionar'])) {
    $produto_id = $_GET['adicionar'];
    $quantidade = $_GET['quantidade'] ?? 1;
    
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        if (isset($_SESSION['carrinho'][$produto_id])) {
            $_SESSION['carrinho'][$produto_id]['quantidade'] += $quantidade;
        } else {
            $_SESSION['carrinho'][$produto_id] = [
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => $quantidade,
                'imagem' => $produto['imagem']
            ];
        }
    }
}

if (isset($_GET['remover'])) {
    unset($_SESSION['carrinho'][$_GET['remover']]);
}

if ($_POST && isset($_POST['atualizar_carrinho'])) {
    foreach ($_POST['quantidades'] as $produto_id => $quantidade) {
        if ($quantidade > 0) {
            $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
        } else {
            unset($_SESSION['carrinho'][$produto_id]);
        }
    }
}

// Calcular totais
$subtotal = 0;
$frete = 0;
foreach ($_SESSION['carrinho'] ?? [] as $item) {
    $subtotal += $item['preco'] * $item['quantidade'];
}

// Frete grátis acima de R$ 99
if ($subtotal >= 99) {
    $frete = 0;
} else {
    $frete = 15.00;
}

$total = $subtotal + $frete;
?>
<?php include 'header.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Meu Carrinho</h1>
        </div>

        <div class="carrinho-layout">
            <div class="carrinho-itens">
                <?php if (empty($_SESSION['carrinho'])): ?>
                    <div class="carrinho-vazio">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p>Adicione produtos incríveis para seu pet!</p>
                        <a href="produtos.php" class="btn btn-primary">Continuar Comprando</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <?php foreach ($_SESSION['carrinho'] as $produto_id => $item): ?>
                        <div class="carrinho-item">
                            <div class="item-imagem">
                                <img src="<?php echo $item['imagem'] ?: 'assets/produto-placeholder.jpg'; ?>" alt="<?php echo $item['nome']; ?>">
                            </div>
                            <div class="item-info">
                                <h3><?php echo $item['nome']; ?></h3>
                                <div class="item-preco">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></div>
                            </div>
                            <div class="item-quantidade">
                                <input type="number" name="quantidades[<?php echo $produto_id; ?>]" 
                                       value="<?php echo $item['quantidade']; ?>" min="1" class="quantidade-input">
                            </div>
                            <div class="item-subtotal">
                                R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                            </div>
                            <div class="item-actions">
                                <a href="carrinho.php?remover=<?php echo $produto_id; ?>" class="btn-remover">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="carrinho-actions">
                            <button type="submit" name="atualizar_carrinho" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Atualizar Carrinho
                            </button>
                            <a href="produtos.php" class="btn btn-outline">Continuar Comprando</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (!empty($_SESSION['carrinho'])): ?>
            <div class="carrinho-resumo">
                <div class="resumo-card">
                    <h3>Resumo do Pedido</h3>
                    <div class="resumo-linha">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                    </div>
                    <div class="resumo-linha">
                        <span>Frete:</span>
                        <span><?php echo $frete > 0 ? 'R$ ' . number_format($frete, 2, ',', '.') : 'Grátis'; ?></span>
                    </div>
                    <div class="resumo-linha total">
                        <span>Total:</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-block">
                        <i class="fas fa-lock"></i> Finalizar Compra
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
<style>
    .carrinho-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 40px;
        margin: 30px 0;
    }

    .carrinho-vazio {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .carrinho-vazio i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .carrinho-item {
        display: grid;
        grid-template-columns: 100px 1fr 120px 100px 60px;
        gap: 20px;
        align-items: center;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }

    .item-imagem img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }

    .item-info h3 {
        margin-bottom: 5px;
        color: #333;
    }

    .item-preco {
        color: #2E7D32;
        font-weight: bold;
    }

    .quantidade-input {
        width: 70px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
    }

    .item-subtotal {
        font-weight: bold;
        color: #333;
    }

    .btn-remover {
        color: #ff4444;
        text-decoration: none;
        font-size: 1.2rem;
    }

    .carrinho-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .btn {
        padding: 12px 20px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #2E7D32;
        color: white;
    }

    .btn-primary:hover {
        background: #1B5E20;
    }

    .btn-secondary {
        background: #666;
        color: white;
    }

    .btn-outline {
        border: 1px solid #2E7D32;
        color: #2E7D32;
        background: white;
    }

    .btn-block {
        display: block;
        width: 100%;
    }

    .resumo-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 100px;
    }

    .resumo-card h3 {
        margin-bottom: 20px;
        color: #333;
    }

    .resumo-linha {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .resumo-linha.total {
        font-size: 1.2rem;
        font-weight: bold;
        color: #2E7D32;
        border-bottom: none;
    }
</style>