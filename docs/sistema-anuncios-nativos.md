# Sistema de Anúncios Nativos - Brasil Hilário

## 📋 Visão Geral

O sistema de anúncios nativos permite exibir anúncios de forma discreta e integrada ao conteúdo do site, misturando-se naturalmente com os posts e sidebar.

## 🏗️ Estrutura do Sistema

### Arquivos Principais

- **`includes/AnunciosManager.php`** - Classe principal para gerenciar anúncios
- **`admin/anuncios.php`** - Dashboard de anúncios no painel admin
- **`admin/novo-anuncio.php`** - Formulário para criar novos anúncios
- **`assets/js/anuncios.js`** - JavaScript para exibição e tracking
- **`api/get-anuncios.php`** - API para buscar anúncios
- **`api/registrar-clique-anuncio.php`** - API para registrar cliques

### Banco de Dados

#### Tabela `anuncios`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- titulo (VARCHAR(255), NOT NULL)
- imagem (VARCHAR(500), NOT NULL)
- link_compra (VARCHAR(500), NOT NULL)
- localizacao (ENUM('sidebar', 'conteudo'), NOT NULL)
- cta_ativo (BOOLEAN, DEFAULT FALSE)
- cta_texto (VARCHAR(100), DEFAULT 'Saiba Mais')
- ativo (BOOLEAN, DEFAULT TRUE)
- criado_em (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- atualizado_em (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
```

#### Tabela `anuncios_posts`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- anuncio_id (INT, NOT NULL, FOREIGN KEY)
- post_id (INT, NOT NULL, FOREIGN KEY)
- UNIQUE KEY unique_anuncio_post (anuncio_id, post_id)
```

#### Tabela `cliques_anuncios`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- anuncio_id (INT, NOT NULL, FOREIGN KEY)
- post_id (INT, NOT NULL, FOREIGN KEY)
- tipo_clique (ENUM('imagem', 'titulo', 'cta'), NOT NULL)
- ip_usuario (VARCHAR(45))
- user_agent (TEXT)
- data_clique (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

## 🎯 Funcionalidades

### 1. Exibição de Anúncios

#### Sidebar
- Anúncios aparecem intercalados com posts da sidebar
- Se há apenas 1 anúncio: exibe após o primeiro post
- Se há múltiplos: intercala a cada 2 posts

#### Conteúdo Principal
- Anúncios exibidos em grid responsivo
- Posicionados após o primeiro post
- Layout adaptável (4-8 cards dependendo do espaço)

### 2. Painel Administrativo

#### Dashboard Principal (`admin/anuncios.php`)
- **Top 5 anúncios mais clicados**
- **Estatísticas gerais**: Total, ativos, sidebar, conteúdo
- **Listagem completa** com thumbnails e stats
- **Ações rápidas**: Editar, excluir

#### Formulário de Criação (`admin/novo-anuncio.php`)
- **Campos obrigatórios**:
  - Título do anúncio
  - Link de compra
  - Localização (sidebar/conteúdo)
  - Upload de imagem
- **Campos opcionais**:
  - CTA (botão de ação)
  - Seleção múltipla de posts
- **Preview em tempo real**
- **Validação completa**

### 3. Tracking de Cliques

#### Tipos de Clique Registrados
- **Imagem**: Clique na imagem do anúncio
- **Título**: Clique no título do anúncio
- **CTA**: Clique no botão de ação

#### Dados Coletados
- ID do anúncio
- ID do post onde foi exibido
- Tipo de clique
- IP do usuário
- User-Agent
- Data/hora do clique

## 🎨 Design e UX

### Características Visuais
- **Selo "PATROCINADO"** discreto no canto superior direito
- **Design nativo** que se mistura ao conteúdo
- **Hover effects** suaves
- **Responsivo** para mobile

### Integração
- **Carregamento assíncrono** via AJAX
- **Não interfere** no carregamento da página
- **Fallback gracioso** se JavaScript estiver desabilitado

## 📊 Relatórios e Analytics

### Dashboard de Performance
- **Top anúncios** por cliques
- **Estatísticas por localização**
- **Métricas por período**
- **Análise de posts** onde anúncios são exibidos

### Exportação de Dados
- **Relatórios CSV** de cliques
- **Análise de conversão** por anúncio
- **Performance por post**

## 🔧 Configuração

### 1. Instalação do Banco
```bash
# Executar o SQL de criação das tabelas
mysql -u usuario -p database < sql/sistema_anuncios_nativos.sql
```

### 2. Configuração de Upload
```php
// Criar diretório para imagens
mkdir -p assets/img/anuncios/
chmod 755 assets/img/anuncios/
```

### 3. Permissões
- **Admin**: Acesso completo ao sistema
- **Editor**: Apenas visualização de relatórios

## 🚀 Como Usar

### Criando um Anúncio
1. Acesse **Admin > Anúncios**
2. Clique em **"Novo Anúncio"**
3. Preencha os campos obrigatórios
4. Selecione a localização (sidebar/conteúdo)
5. Faça upload da imagem
6. Configure o CTA (opcional)
7. Selecione os posts onde será exibido
8. Salve o anúncio

### Monitorando Performance
1. Acesse **Admin > Anúncios**
2. Visualize o **Top 5** no dashboard
3. Clique em **"Ver Todos"** para lista completa
4. Analise cliques por anúncio

## 🔒 Segurança

### Validações
- **Upload de imagens**: Apenas formatos permitidos
- **Links**: Validação de URL
- **Permissões**: Verificação de acesso admin
- **SQL Injection**: Prepared statements

### Proteções
- **Rate limiting** para cliques
- **Validação de IP** para evitar spam
- **Sanitização** de dados de entrada
- **Logs de erro** para debugging

## 📱 Responsividade

### Mobile
- **Grid adaptativo** para anúncios
- **Touch-friendly** botões
- **Imagens otimizadas** para mobile
- **Performance otimizada**

### Desktop
- **Layout em grid** para anúncios
- **Hover effects** interativos
- **Integração perfeita** com conteúdo

## 🔄 Manutenção

### Limpeza de Dados
- **Logs antigos**: Remover cliques com mais de 1 ano
- **Imagens órfãs**: Limpar uploads não utilizados
- **Anúncios inativos**: Arquivar após 6 meses

### Backup
- **Dados críticos**: Backup diário das tabelas
- **Imagens**: Backup semanal do diretório
- **Logs**: Backup mensal de cliques

## 🎯 Benefícios

### Para o Site
- **Receita adicional** sem comprometer UX
- **Anúncios nativos** que não afastam usuários
- **Controle total** sobre exibição
- **Analytics detalhados**

### Para Anunciantes
- **Visibilidade natural** no conteúdo
- **Engajamento alto** com público relevante
- **Métricas transparentes** de performance
- **Flexibilidade** de localização

## 📈 Métricas de Sucesso

### KPIs Principais
- **CTR (Click-Through Rate)**: Meta > 2%
- **Engajamento**: Tempo de visualização
- **Conversão**: Cliques que geram vendas
- **Satisfação**: Feedback dos usuários

### Otimização Contínua
- **A/B testing** de títulos e imagens
- **Análise de horários** de melhor performance
- **Segmentação** por tipo de post
- **Personalização** baseada em comportamento

---

**Desenvolvido para o projeto Brasil Hilário**  
*Sistema de anúncios nativos profissional e integrado* 