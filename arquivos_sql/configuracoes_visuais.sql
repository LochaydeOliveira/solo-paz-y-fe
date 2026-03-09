-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 08/08/2025 às 10:41
-- Versão do servidor: 5.7.23-23
-- Versão do PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `paymen58_brasil_hilario`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_visuais`
--

CREATE TABLE `configuracoes_visuais` (
  `id` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `elemento` varchar(100) NOT NULL,
  `propriedade` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `tipo` enum('cor','fonte','numero','texto','boolean') DEFAULT 'texto',
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `configuracoes_visuais`
--

INSERT INTO `configuracoes_visuais` (`id`, `categoria`, `elemento`, `propriedade`, `valor`, `tipo`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(1, 'cores', 'site', 'cor_primaria', '#00050a', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(2, 'cores', 'site', 'cor_secundaria', '#000000', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(3, 'cores', 'site', 'cor_sucesso', '#28a745', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(4, 'cores', 'site', 'cor_perigo', '#dc3545', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(5, 'cores', 'site', 'cor_aviso', '#ffc107', 'cor', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(6, 'cores', 'site', 'cor_info', '#17a2b8', 'cor', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(7, 'cores', 'header', 'cor_fundo', '#f8f9f4', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(8, 'cores', 'header', 'cor_texto', '#333333', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(9, 'cores', 'header', 'cor_link', '#5c5c5c', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(10, 'cores', 'header', 'cor_link_hover', '#0056b3', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(11, 'cores', 'footer', 'cor_fundo', '#f8f9fa', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(12, 'cores', 'footer', 'cor_texto', '#6c757d', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(13, 'cores', 'footer', 'cor_link', '#009447', 'cor', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(14, 'fontes', 'site', 'fonte_principal', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(15, 'fontes', 'site', 'fonte_titulos', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(16, 'fontes', 'site', 'fonte_texto', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(17, 'fontes', 'titulo', 'tamanho', '28px', 'texto', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(18, 'fontes', 'subtitulo', 'tamanho', '20px', 'texto', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(19, 'fontes', 'paragrafo', 'tamanho', '16px', 'texto', 1, '2025-07-28 03:43:13', '2025-08-07 00:52:13'),
(20, 'fontes', 'pequeno', 'tamanho', '14px', 'texto', 1, '2025-07-28 03:43:13', '2025-07-28 03:43:13'),
(80, 'fontes', 'site', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 06:26:18', '2025-08-07 00:52:13'),
(81, 'fontes', 'titulo', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 06:26:18', '2025-08-07 00:52:13'),
(82, 'fontes', 'paragrafo', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-28 06:26:18', '2025-08-07 00:52:13'),
(103, 'cores', 'paginacao', 'cor_fundo', '#ffffff', 'cor', 1, '2025-07-28 07:07:23', '2025-07-28 07:39:12'),
(104, 'cores', 'paginacao', 'cor_texto', '#007bff', 'cor', 1, '2025-07-28 07:07:23', '2025-07-28 07:39:12'),
(105, 'cores', 'paginacao', 'cor_link', '#007bff', 'cor', 1, '2025-07-28 07:07:23', '2025-07-28 07:39:12'),
(106, 'cores', 'paginacao', 'cor_ativa', '#007bff', 'cor', 1, '2025-07-28 07:07:23', '2025-07-28 07:39:12'),
(118, 'cores', 'botao', 'cor_primario', '#007bff', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(119, 'cores', 'botao', 'cor_secundario', '#6c757d', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(120, 'cores', 'botao', 'cor_sucesso', '#28a745', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(121, 'cores', 'card', 'cor_fundo', '#ffffff', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(122, 'cores', 'card', 'cor_borda', '#dee2e6', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(123, 'cores', 'card', 'cor_texto', '#212529', 'cor', 1, '2025-07-28 07:18:57', '2025-08-07 00:52:13'),
(627, 'fontes', 'card', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-30 06:59:18', '2025-08-07 00:52:13'),
(628, 'fontes', 'sidebar', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-30 06:59:18', '2025-08-07 00:52:13'),
(629, 'fontes', 'meta', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-30 06:59:18', '2025-08-07 00:52:13'),
(630, 'fontes', 'botao', 'fonte', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'fonte', 1, '2025-07-30 06:59:18', '2025-08-07 00:52:13');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `configuracoes_visuais`
--
ALTER TABLE `configuracoes_visuais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_config` (`categoria`,`elemento`,`propriedade`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `configuracoes_visuais`
--
ALTER TABLE `configuracoes_visuais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=958;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
