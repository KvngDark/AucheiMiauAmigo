<?php
include 'conexao.php';

// Redirecionar se já estiver logado
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: perfil.php");
    }
    exit;
}

// Processar cadastro
if ($_POST && isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $tipo = $_POST['tipo'];
    $cep = trim($_POST['cep']);
    $logradouro = trim($_POST['logradouro']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $sobre = trim($_POST['sobre']);
    
    // Validar senhas
    if ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    }
    
    // Garantir que o tipo seja apenas tutor ou ong
    if (!in_array($tipo, ['tutor', 'ong'])) {
        $tipo = 'tutor';
    }
    
    // Montar endereço completo
    $endereco = $logradouro . ', ' . $numero;
    if (!empty($complemento)) {
        $endereco .= ' - ' . $complemento;
    }
    $endereco .= ' - CEP: ' . $cep;
    
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $erro = "Este email já está cadastrado.";
    } elseif (!isset($erro)) {
        // Processar upload da foto de perfil
        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImagem($_FILES['foto_perfil'], 'perfis/');
            if ($upload['success']) {
                $foto_perfil = $upload['caminho'];
            } else {
                $erro = $upload['message'];
            }
        }
        
        if (!isset($erro)) {
            // Inserir usuário com senha (sem criptografia)
            $sql = "INSERT INTO usuarios (nome, email, telefone, tipo, endereco, sobre, foto_perfil, cep, logradouro, numero, complemento, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $telefone, $tipo, $endereco, $sobre, $foto_perfil, $cep, $logradouro, $numero, $complemento, $senha]);
            
            $usuario_id = $pdo->lastInsertId();
            
            // Logar automaticamente
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = $tipo;
            
            header("Location: perfil.php");
            exit;
        }
    }
}

// Processar login
if ($_POST && isset($_POST['login'])) {
    $email = trim($_POST['email_login']);
    $senha = trim($_POST['senha_login']);
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND senha = ?");
    $stmt->execute([$email, $senha]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        
        // Redirecionar admin para painel administrativo
        if ($usuario['tipo'] == 'admin') {
            header("Location: admin.php");
            exit;
        }
        
        header("Location: perfil.php");
        exit;
    } else {
        $erro_login = "Email ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - PetShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #2E7D32, #4CAF50);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }
        
        .login-sidebar {
            background: linear-gradient(135deg, #2E7D32, #4CAF50);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .login-sidebar h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .login-sidebar p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .login-form {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom: 3px solid #2E7D32;
            color: #2E7D32;
        }
        
        .form-content {
            display: none;
        }
        
        .form-content.active {
            display: block;
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
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #2E7D32;
            outline: none;
        }
        
        .btn {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
            font-weight: 500;
        }
        
        .btn:hover {
            background: #1B5E20;
        }
        
        .erro {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-sidebar">
            <h1>Bem-vindo ao PetShop</h1>
            <p>Tudo que seu pet precisa em um só lugar!</p>
        </div>
        
        <div class="login-form">
            <div class="form-tabs">
                <div class="tab active" onclick="mostrarForm('login')">Entrar</div>
                <div class="tab" onclick="mostrarForm('cadastro')">Cadastrar</div>
            </div>
            
            <!-- Formulário de Login -->
            <div id="login-form" class="form-content active">
                <h2 style="margin-bottom: 20px; color: #333;">Fazer Login</h2>
                <?php if (isset($erro_login)): ?>
                    <div class="erro"><?php echo $erro_login; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email_login">Email:</label>
                        <input type="email" id="email_login" name="email_login" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha_login">Senha:</label>
                        <input type="password" id="senha_login" name="senha_login" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn">Entrar</button>
                </form>
            </div>
            
            <!-- Formulário de Cadastro -->
            <div id="cadastro-form" class="form-content">
                <h2 style="margin-bottom: 20px; color: #333;">Criar Conta</h2>
                <?php if (isset($erro)): ?>
                    <div class="erro"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Celular *</label>
                        <input type="text" id="telefone" name="telefone" required oninput="formatarTelefone(this)">
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="password" id="senha" name="senha" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha *</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="cep">CEP *</label>
                        <input type="text" id="cep" name="cep" required onblur="buscarEndereco(this.value)">
                    </div>
                    
                    <div class="form-group">
                        <label for="logradouro">Logradouro *</label>
                        <input type="text" id="logradouro" name="logradouro" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="numero">Número *</label>
                        <input type="text" id="numero" name="numero" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="complemento">Complemento</label>
                        <input type="text" id="complemento" name="complemento">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo de Conta *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="tutor">Tutor</option>
                            <option value="ong">ONG</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sobre">Sobre</label>
                        <textarea id="sobre" name="sobre" placeholder="Conte um pouco sobre você ou sua ONG..."></textarea>
                    </div>
                    
                    <button type="submit" name="cadastrar" class="btn">Criar Conta</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mostrarForm(form) {
            document.querySelectorAll('.form-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            
            if (form === 'login') {
                document.getElementById('login-form').classList.add('active');
                document.querySelectorAll('.tab')[0].classList.add('active');
            } else {
                document.getElementById('cadastro-form').classList.add('active');
                document.querySelectorAll('.tab')[1].classList.add('active');
            }
        }
        
        function formatarTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            input.value = value;
        }

        function buscarEndereco(cep) {
            cep = cep.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('complemento').value = data.complemento;
                            document.getElementById('numero').focus();
                        }
                    })
                    .catch(error => console.error('Erro ao buscar CEP:', error));
            }
        }
    </script>
</body>
</html>