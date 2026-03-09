# Script para criar pastas para todos os posts
$pastas = @(
    "post-8-esporte-transformacao-social",
    "post-9-7-beneficios-da-musculacao", 
    "post-10-de-icones-globais-a-referencias-de-estilo",
    "post-11-top-7-inovacoes-agronegocio-2025",
    "post-12-brasil-2025-10-tendencias",
    "post-13-filmes-esperados-2025",
    "post-14-obstrucao-justica",
    "post-15-alerta-oriente-medio",
    "post-16-tardigrados-animal-resistente",
    "post-17-granulos-fordyce-bolinhas",
    "post-19-instituto-weizmann-ciencia-guerra",
    "post-20-imunoterapia-cancer",
    "post-21-cultura-cancelamento",
    "post-22-andressa-urach-olhos",
    "post-23-habito-protege-cerebro-memoria",
    "post-24-emprego-infelicidade-brasil",
    "post-25-jose-maria-marin-cbf",
    "post-26-preta-gil-morte",
    "post-27-pressao-externa-politica",
    "post-28-coral-invasor-baia-todos-santos",
    "post-29-diplomacia-digital-politica",
    "post-30-vacinacao-saude-cerebral",
    "post-31-brasil-paraguai-copa-america",
    "post-32-terra-gira-mais-rapido",
    "post-33-policia-civil-oruam-rj",
    "post-34-prisao-preventiva-medidas-cautelares",
    "post-35-sao-paulo-fria-neblina",
    "post-36-conflito-acoes-judiciais-liberdade-expressao",
    "post-37-joey-jones-liverpool-morte",
    "post-38-palestina-mortes-fome-gaza",
    "post-39-72-brasileiros-conflitos-mundiais-economia",
    "post-40-7500-exoplanetas-nasa",
    "post-41-tubarao-boca-grande-sergipe",
    "post-43-exportacoes-carne-bovina-eua",
    "post-44-itau-banco-digital-ia",
    "post-45-nordeste-rio-culinaria",
    "post-46-corinthians-saidas-elenco",
    "post-47-fx4-agro-inovacao-genetica",
    "post-48-roger-waters-cinemas",
    "post-49-conta-luz-tecnologia-energia-limpa",
    "post-50-navio-britanico-250-anos",
    "post-51-vinhos-nova-zelandia-brasil",
    "post-52-dancar-pecado-danca",
    "post-53-neymar-santos-torcedor",
    "post-54-asteroide-67-metros-terra",
    "post-55-pesquisa-58-brasileiros-economia",
    "post-56-agronegocio-brasil-perder-6bi",
    "post-57-fortaleza-bragantino-brasileirao",
    "post-58-senadores-eua-tarifa",
    "post-59-palmeiras-gremio-brasileirao",
    "post-60-homens-dancam-desejo",
    "post-61-bomba-suja-arma-radiologica",
    "post-62-objeto-interestelar-3i-atlas",
    "post-63-simbolismo-ataque-igreja-gaza",
    "post-64-cientistas-alertam-amostras-marte",
    "post-65-russia-invadir-moldavia",
    "post-67-ossos-humanos-titanic",
    "post-68-10-profissoes-psicopatas",
    "post-69-roberta-miranda-chute-palco",
    "post-70-esporte-politica-polarizacao",
    "post-71-impeachment-ministro-stf",
    "post-72-adultizacao-infantil-redes-sociais",
    "post-73-algoritmos-exposicao-criancas",
    "post-74-cnh-sem-autoescola"
)

foreach ($pasta in $pastas) {
    $caminho = "img_posts\$pasta"
    if (!(Test-Path $caminho)) {
        New-Item -ItemType Directory -Path $caminho -Force
        Write-Host "Criada pasta: $caminho"
    } else {
        Write-Host "Pasta já existe: $caminho"
    }
}

Write-Host "Processo concluído!" 