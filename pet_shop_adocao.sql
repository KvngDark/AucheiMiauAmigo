-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/11/2025 às 22:08
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pet_shop_adocao`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `animais`
--

CREATE TABLE `animais` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo_animal_id` int(11) DEFAULT NULL,
  `raca` varchar(100) DEFAULT NULL,
  `idade` int(11) DEFAULT NULL,
  `sexo` enum('M','F') DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `data_entrada` date DEFAULT NULL,
  `adotado` tinyint(1) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `adotado_por` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`) VALUES
(1, 'Ração', 'Alimentos para animais'),
(2, 'Brinquedos', 'Brinquedos para entretenimento'),
(3, 'Higiene', 'Produtos de higiene e limpeza'),
(4, 'Acessórios', 'Coleiras, guias, camas, etc.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `data_contratacao` date DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `nome`, `email`, `telefone`, `cargo`, `data_contratacao`, `salario`, `ativo`) VALUES
(1, 'João Silva', 'joao@email.com', '(11) 9999-8888', 'Gerente', '2020-01-15', 3500.00, 1),
(2, 'Maria Santos', 'maria@email.com', '(11) 7777-6666', 'Vendedor', '2021-03-20', 2200.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `quantidade_estoque` int(11) DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `destaque` tinyint(4) DEFAULT 0,
  `categoria` varchar(100) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `quantidade_estoque`, `categoria_id`, `fornecedor`, `data_cadastro`, `destaque`, `categoria`, `imagem`, `created_at`) VALUES
(1, 'Ração Premier para Cães', 'Ração premium para cães adultos, sabor frango e arroz', 89.90, 50, NULL, NULL, NULL, 1, 'racao', NULL, '2025-11-04 22:01:59'),
(2, 'Brinquedo Pelúcia para Gatos', 'Brinquedo interativo em formato de peixe com catnip', 24.90, 30, NULL, NULL, NULL, 1, 'brinquedos', NULL, '2025-11-04 22:01:59'),
(3, 'Shampoo Antipulgas', 'Shampoo para cães e gatos com proteção contra pulgas e carrapatos', 32.50, 25, NULL, NULL, NULL, 0, 'higiene', NULL, '2025-11-04 22:01:59'),
(4, 'Coleira Antipulgas', 'Coleira ajustável com proteção contra pulgas por 8 meses', 45.90, 40, NULL, NULL, NULL, 1, 'saude', NULL, '2025-11-04 22:01:59'),
(5, 'Ração Whiskas para Gatos', 'Ração úmida para gatos adultos, sabor carne', 5.90, 100, NULL, NULL, NULL, 1, 'racao', NULL, '2025-11-04 22:01:59'),
(6, 'Osso de Nylon para Cães', 'Osso dental que ajuda na limpeza dos dentes', 18.90, 35, NULL, NULL, NULL, 0, 'brinquedos', NULL, '2025-11-04 22:01:59'),
(7, 'Areia Sanitária Cristal', 'Areia cristal com alta absorção e controle de odor', 39.90, 60, NULL, NULL, NULL, 1, 'higiene', NULL, '2025-11-04 22:01:59'),
(8, 'Vermífugo para Cães', 'Vermífugo de amplo espectro para cães de todas as idades', 28.50, 20, NULL, NULL, NULL, 0, 'saude', NULL, '2025-11-04 22:01:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_animais`
--

CREATE TABLE `tipos_animais` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos_animais`
--

INSERT INTO `tipos_animais` (`id`, `nome`, `descricao`) VALUES
(1, 'Cachorro', 'Animais caninos'),
(2, 'Gato', 'Animais felinos'),
(3, 'Pássaro', 'Aves domésticas'),
(4, 'Réptil', 'Animais répteis'),
(5, 'Roedor', 'Pequenos mamíferos roedores'),
(6, 'Exótico', 'Outros animais exóticos'),
(7, 'Cachorro', NULL),
(8, 'Gato', NULL),
(9, 'Pássaro', NULL),
(10, 'Peixe', NULL),
(11, 'Roedor', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `tipo` enum('tutor','ong','admin') DEFAULT 'tutor',
  `endereco` text DEFAULT NULL,
  `sobre` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `cep` varchar(10) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL DEFAULT '123456'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `tipo`, `endereco`, `sobre`, `foto_perfil`, `data_cadastro`, `cep`, `logradouro`, `numero`, `complemento`, `senha`) VALUES
(1, 'Ana Oliveira', 'ana@email.com', '(11) 8888-7777', 'tutor', 'Rua das Flores, 123 - São Paulo', 'Amo animais e tenho experiência com cães e gatos.', NULL, '2025-11-03 21:02:37', NULL, NULL, NULL, NULL, '123456'),
(2, 'ONG Amigos dos Bichos', 'ong@amigosdosbichos.com', '(11) 3333-4444', 'ong', 'Av. Principal, 456 - São Paulo', 'ONG dedicada ao resgate e adoção de animais abandonados.', NULL, '2025-11-03 21:02:37', NULL, NULL, NULL, NULL, '123456'),
(3, 'Carlos Mendes', 'carlos@email.com', '(11) 5555-6666', 'tutor', 'Rua do Parque, 789 - São Paulo', 'Criador de animais exóticos com mais de 10 anos de experiência.', NULL, '2025-11-03 21:02:37', NULL, NULL, NULL, NULL, '123456'),
(5, 'Administrador', 'admin@petshop.com', '(11) 99999-9999', 'admin', 'Sistema', 'Usuário administrador do sistema', NULL, '2025-11-05 18:03:45', NULL, NULL, NULL, NULL, 'admin123');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `funcionario_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `animais`
--
ALTER TABLE `animais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_animal_id` (`tipo_animal_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `adotado_por` (`adotado_por`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `tipos_animais`
--
ALTER TABLE `tipos_animais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `animais`
--
ALTER TABLE `animais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `tipos_animais`
--
ALTER TABLE `tipos_animais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `animais`
--
ALTER TABLE `animais`
  ADD CONSTRAINT `animais_ibfk_1` FOREIGN KEY (`tipo_animal_id`) REFERENCES `tipos_animais` (`id`),
  ADD CONSTRAINT `animais_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `animais_ibfk_3` FOREIGN KEY (`adotado_por`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
