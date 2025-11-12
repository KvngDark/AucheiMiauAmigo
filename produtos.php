<?php
session_start();
include 'conexao.php';

$categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';

// Construir query
$sql = "SELECT * FROM produtos WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$sql .= " ORDER BY nome";

if (empty($params)) {
    $produtos = $pdo->query($sql)->fetchAll();
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll();
}
?>
<?php include 'header.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Nossos Produtos</h1>
            <p>Encontre tudo que seu pet precisa</p>
        </div>

        <div class="filtros">
            <div class="filtro-categorias">
                <strong>Categorias:</strong>
                <a href="produtos.php" class="<?php echo empty($categoria) ? 'active' : ''; ?>">Todos</a>
                <a href="produtos.php?categoria=racao" class="<?php echo $categoria == 'racao' ? 'active' : ''; ?>">Ração</a>
                <a href="produtos.php?categoria=brinquedos" class="<?php echo $categoria == 'brinquedos' ? 'active' : ''; ?>">Brinquedos</a>
                <a href="produtos.php?categoria=higiene" class="<?php echo $categoria == 'higiene' ? 'active' : ''; ?>">Higiene</a>
                <a href="produtos.php?categoria=saude" class="<?php echo $categoria == 'saude' ? 'active' : ''; ?>">Saúde</a>
            </div>
        </div>

        <div class="produtos-lista">
            <?php if (count($produtos) > 0): ?>
                <div class="produtos-grid">
                    <?php foreach($produtos as $produto): ?>
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
                            <p class="produto-desc"><?php echo $produto['descricao']; ?></p>
                            <div class="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                            <div class="produto-estoque">Estoque: <?php echo $produto['quantidade_estoque']; ?> unidades</div>
                            <div class="produto-actions">
                                <?php if ($produto['quantidade_estoque'] > 0): ?>
                                    <button class="btn-adicionar" onclick="adicionarAoCarrinho(<?php echo $produto['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Adicionar
                                    </button>
                                <?php else: ?>
                                    <div class="sem-estoque">Fora de estoque</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sem-resultados">
                    <i class="fas fa-search"></i>
                    <h3>Nenhum produto encontrado</h3>
                    <p>Tente ajustar os filtros de busca.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .produtos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .produto-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .produto-card:hover {
        transform: translateY(-5px);
    }

    .produto-imagem {
        height: 200px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .produto-imagem img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .produto-imagem span {
        font-size: 3rem;
        color: #ddd;
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

    .produto-info {
        padding: 20px;
    }

    .produto-info h3 {
        margin-bottom: 10px;
        color: #333;
        font-size: 1.1rem;
    }

    .produto-desc {
        color: #666;
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .produto-preco {
        font-size: 1.3rem;
        font-weight: bold;
        color: #2E7D32;
        margin-bottom: 10px;
    }

    .produto-estoque {
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
    }

    .btn-adicionar {
        background: #2E7D32;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        transition: background 0.3s;
    }

    .btn-adicionar:hover {
        background: #1B5E20;
    }

    .sem-estoque {
        background: #ffebee;
        color: #c62828;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        font-weight: 500;
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

<?php include 'footer.php'; ?>