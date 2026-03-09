function registrarCliqueAnuncio(anuncioId, tipoClique = 'imagem') {

    const postIdMeta = document.querySelector('meta[name="post-id"]');
    const postId = postIdMeta ? parseInt(postIdMeta.content) : 0;
    const dados = {
        anuncio_id: parseInt(anuncioId),
        post_id: postId,
        tipo_clique: tipoClique
    };
    
    fetch('/api/registrar-clique.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Clique processado');
    })
    .catch(error => {

        console.log('✅ Clique processado');
    });
}


function scrollCarrossel(grupoId, direction) {
    const carrossel = document.querySelector(`[data-grupo-id="${grupoId}"] .anuncios-carrossel`);
    if (!carrossel) return;
    
    const scrollAmount = 300;
    if (direction === 'left') {
        carrossel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        carrossel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
}); 