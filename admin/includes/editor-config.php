<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';

// Configuração do TinyMCE
$editor_config = [
    'selector' => '#editor',
    'height' => 500,
    'plugins' => [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    'toolbar' => 'undo redo | blocks | ' .
                'bold italic backcolor | alignleft aligncenter ' .
                'alignright alignjustify | bullist numlist outdent indent | ' .
                'image media | removeformat | help',
    'content_style' => 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; }',
    'images_upload_url' => 'upload-image.php',
    'images_reuse_filename' => true
];
?>
