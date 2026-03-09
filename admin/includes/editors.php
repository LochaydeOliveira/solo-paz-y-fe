<?php
// Configurações do TinyMCE
function get_tinymce_config() {
    return [
        'selector' => '#conteudo',
        'height' => 500,
        'plugins' => 'advlist autolink lists link image charmap print preview anchor \
                    searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount imagetools',
        'toolbar' => 'undo redo | formatselect | \
                    bold italic backcolor | alignleft aligncenter \
                    alignright alignjustify | bullist numlist outdent indent | \
                    removeformat | help | image',
        'content_css' => ASSETS_URL . '/css/style.css',
        'images_upload_url' => API_URL . '/upload.php',
        'relative_urls' => false,
        'remove_script_host' => false,
        'document_base_url' => BLOG_URL . '/',
        'tinymce_base_url' => ASSETS_URL . '/tinymce', // Certifique-se de que o caminho para o TinyMCE está correto
        'automatic_uploads' => true,
        'file_picker_types' => 'image',
        'file_picker_callback' => <<<JS
function(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');

    input.onchange = function() {
        var file = this.files[0];

        var reader = new FileReader();
        reader.onload = function () {
            var id = 'blobid' + (new Date()).getTime();
            var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
            var base64 = reader.result.split(',')[1];
            var blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);

            cb(blobInfo.blobUri(), { title: file.name });
        };
        reader.readAsDataURL(file);
    };

    input.click();
}
JS
        ,
        'api_key' => 'wg7esjmgm070id61pbr1sisy7ijfgyhcr7dg5t660r2esvb5' // Adicione sua chave de API do TinyMCE aqui
    ];
}

// Configurações do Markdown
function get_markdown_config() {
    return [
        'element' => '#conteudo',
        'autoSave' => true,
        'spellChecker' => false,
        'toolbar' => 'bold italic | alignleft aligncenter alignright | link image | preview',
        'imageUpload' => true,
        'imageUploadEndpoint' => API_URL . '/upload.php'
    ];
}

// Função para carregar os scripts necessários
function load_editor_scripts($editor_type = 'tinymce') {
    echo '<script src="' . ASSETS_URL . '/js/jquery-3.6.0.min.js"></script>'; // Garantir jQuery carregado
    if ($editor_type === 'tinymce') {
        echo '<script src="https://cdn.tiny.cloud/1/' . get_tinymce_config()['api_key'] . '/tinymce.min.js" referrerpolicy="origin"></script>';
        echo '<script>
            tinymce.init(' . json_encode(get_tinymce_config()) . ');
        </script>';
    } elseif ($editor_type === 'markdown') {
        echo '<script src="' . ASSETS_URL . '/js/easymde.min.js"></script>';
        echo '<link rel="stylesheet" href="' . ASSETS_URL . '/css/easymde.min.css">';
        echo '<script>
            var easymde = new EasyMDE(' . json_encode(get_markdown_config()) . ');
        </script>';
    }
} 