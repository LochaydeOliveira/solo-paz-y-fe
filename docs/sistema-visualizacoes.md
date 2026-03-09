# Sistema de Contagem de Visualizações

## Visão Geral

O sistema de contagem de visualizações foi implementado para garantir que apenas **visualizações reais** sejam contabilizadas, excluindo administradores, bots e visualizações duplicadas.

## Como Funciona

### 1. Verificação de Administradores Logados
- **Filtro**: Usuários logados como `admin` ou `editor`
- **Método**: Verificação de sessão PHP
- **Resultado**: Visualizações não contadas

### 2. Detecção de Bots
- **Filtro**: User-Agent contendo padrões de bots
- **Padrões**: `bot`, `crawler`, `spider`, `googlebot`, `facebookexternalhit`, etc.
- **Resultado**: Visualizações não contadas

### 3. Filtro de IPs de Administradores
- **IPs**: `179.48.2.57`, `127.0.0.1`, `::1`, etc.
- **Método**: Lista de IPs conhecidos
- **Resultado**: Visualizações não contadas

### 4. Prevenção de Contagem Duplicada
- **Método**: Cookies com duração de 24 horas
- **Nome**: `viewed_post_[slug]`
- **Resultado**: Evita contagem múltipla do mesmo usuário

### 5. Filtro de Requisições AJAX
- **Filtro**: Requisições com header `X-Requested-With`
- **Motivo**: Pode ser bot ou script automatizado
- **Resultado**: Visualizações não contadas

## Arquivos do Sistema

### Configuração Principal
- `config/admin_ips.php` - IPs e padrões de filtro
- `config/view_logger.php` - Sistema de log (opcional)

### Implementação
- `post.php` - Contagem na página do post
- `api/incrementar-visualizacao.php` - API de contagem

## Logs e Monitoramento

### Arquivos de Log (Opcional)
- `logs/filtered_views.log` - Visualizações filtradas
- `logs/counted_views.log` - Visualizações contadas

### Estatísticas Disponíveis
- Visualizações filtradas por motivo
- Total de visualizações reais
- IPs e User-Agents filtrados

## Como Adicionar Novos IPs

Para adicionar novos IPs de administradores, edite o arquivo `config/admin_ips.php`:

```php
$ADMIN_IPS = [
    '179.48.2.57', // IP atual do admin
    '127.0.0.1',   // Localhost
    '::1',         // Localhost IPv6
    'SEU_NOVO_IP', // Adicione aqui
];
```

## Como Adicionar Novos Padrões de Bot

Para adicionar novos padrões de User-Agent, edite o arquivo `config/admin_ips.php`:

```php
$BOT_PATTERNS = [
    'bot', 'crawler', 'spider',
    'novo_padrao_bot', // Adicione aqui
];
```

## Benefícios

1. **Estatísticas Precisas**: Apenas visualizações reais são contadas
2. **Prevenção de Inflação**: Bots e administradores são filtrados
3. **Monitoramento**: Logs para debug e análise
4. **Flexibilidade**: Fácil adição de novos filtros
5. **Performance**: Sistema otimizado e eficiente

## Testando o Sistema

Para verificar se o sistema está funcionando:

1. Acesse um post como administrador - não deve contar
2. Acesse como usuário normal - deve contar
3. Verifique os logs em `logs/` (se habilitado)
4. Monitore as estatísticas no painel admin

## Troubleshooting

### Visualizações não estão sendo contadas
- Verifique se não está logado como admin
- Verifique se o IP não está na lista de exclusão
- Verifique se não há cookie de visualização recente

### Logs não estão sendo gerados
- Verifique se o diretório `logs/` existe
- Verifique permissões de escrita
- Verifique se o sistema de log está habilitado 