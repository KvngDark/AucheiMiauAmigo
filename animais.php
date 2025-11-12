<?php
session_start();
include 'conexao.php';

// Buscar animais disponíveis
$sql = "SELECT a.*, t.nome as tipo_nome, u.nome as usuario_nome 
        FROM animais a 
        LEFT JOIN tipos_animais t ON a.tipo_animal_id = t.id 
        LEFT JOIN usuarios u ON a.usuario_id = u.id 
        WHERE a.adotado = 0 
        ORDER BY a.data_entrada DESC";
$animais = $pdo->query($sql)->fetchAll();

// Buscar tipos de animais para filtro
$tipos_animais = $pdo->query("SELECT * FROM tipos_animais ORDER BY nome")->fetchAll();
?>
<?php include 'header.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Amigos para Adoção</h1>
            <p>Encontre seu novo companheiro</p>
        </div>

        <!-- Filtros -->
        <div class="filtros">
            <div class="filtro-categorias">
                <strong>Filtrar por:</strong>
                <a href="animais.php" class="active">Todos</a>
                <?php foreach($tipos_animais as $tipo): ?>
                    <a href="animais.php?tipo=<?php echo $tipo['id']; ?>"><?php echo $tipo['nome']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lista de Animais -->
        <div class="animais-lista">
            <?php if (count($animais) > 0): ?>
                <div class="animais-grid">
                    <?php foreach($animais as $animal): ?>
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
                            <div class="animal-details">
                                <p><strong>Espécie:</strong> <?php echo $animal['tipo_nome']; ?></p>
                                <p><strong>Raça:</strong> <?php echo $animal['raca'] ?: 'Não informada'; ?></p>
                                <p><strong>Idade:</strong> <?php echo $animal['idade']; ?> anos</p>
                                <p><strong>Sexo:</strong> <?php echo $animal['sexo'] == 'M' ? 'Macho' : 'Fêmea'; ?></p>
                            </div>
                            <p class="animal-desc"><?php echo substr($animal['descricao'], 0, 100) . '...'; ?></p>
                            <div class="animal-actions">
                                <a href="detalhes_animal.php?id=<?php echo $animal['id']; ?>" class="btn-adotar">Conhecer</a>
                                <span class="responsavel">Por: <?php echo $animal['usuario_nome']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sem-resultados">
                    <i class="fas fa-paw"></i>
                    <h3>Nenhum animal disponível</h3>
                    <p>No momento não temos animais para adoção.</p>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="cadastrar_animal.php" class="btn-primary">Cadastrar Animal</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .animais-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .animal-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .animal-card:hover {
        transform: translateY(-5px);
    }

    .animal-imagem {
        height: 250px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .animal-imagem img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .animal-imagem span {
        font-size: 4rem;
        color: #ddd;
    }

    .animal-info {
        padding: 20px;
    }

    .animal-info h3 {
        color: #2E7D32;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .animal-details {
        margin-bottom: 15px;
    }

    .animal-details p {
        margin-bottom: 5px;
        font-size: 14px;
        color: #666;
    }

    .animal-desc {
        color: #777;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 15px;
    }

    .animal-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-adotar {
        background: #FF9800;
        color: white;
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s;
    }

    .btn-adotar:hover {
        background: #F57C00;
    }

    .responsavel {
        font-size: 12px;
        color: #999;
    }

    .sem-resultados {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .sem-resultados i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }
</style>

<?php include 'footer.php'; ?>