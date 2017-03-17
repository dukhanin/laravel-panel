<?php

return [
    'default' => [
        'language'                  => 'en',
        'relative_urls'             => false,
        'menubar'                   => false,
        'plugins'                   => [
            'advlist',
            'autolink',
            'lists',
            'link',
            'image',
            'charmap',
            'print',
            'preview',
            'anchor',
            'searchreplace',
            'visualblocks',
            'code',
            'fullscreen',
            'insertdatetime',
            'media',
            'table',
            'contextmenu',
            'paste',
            'responsivefilemanager',
            'fullscreen'
        ],
        'toolbar'                   => [
            'insertfile undo redo | styleselect | bold italic |  bullist numlist | code fullscreen',
            'link responsivefilemanager image | alignleft aligncenter alignright alignjustify | outdent indent',
        ],
        'content_css'               => [
            '/assets/css/wysiwyg.css',
            'http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800',
            'http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic',
        ],
        'external_filemanager_path' => '/assets/filemanager/',
        'filemanager_title'         => 'Browse files',
        'external_plugins'          => [ 'filemanager' => '/assets/filemanager/plugin.min.js' ],
        'image_advtab'              => true,
        'paste_data_images'         => true,
        'images_upload_url'         => '/panel/upload/tinymce',
        'images_upload_credentials' => true,
        'custom_elements'           => 'noindex',
        'extend_valid_elements'     => '',
        'style_formats'             => [
            [
                'title' => 'Headings',
                'items' => [
                    [ 'title' => 'Heading 1', 'format' => 'h1' ],
                    [ 'title' => 'Heading 2', 'format' => 'h2' ],
                    [ 'title' => 'Heading 3', 'format' => 'h3' ],
                    [ 'title' => 'Heading 4', 'format' => 'h4' ],
                    [ 'title' => 'Heading 5', 'format' => 'h5' ],
                    [ 'title' => 'Heading 6', 'format' => 'h6' ],
                ]
            ],
            [
                'title' => 'Inline',
                'items' => [
                    [ 'title' => 'Bold', 'icon' => 'bold', 'format' => 'bold' ],
                    [ 'title' => 'Fake-Bold', 'icon' => 'bold', 'format' => 'fakebold' ],
                    [ 'title' => 'Italic', 'icon' => 'italic', 'format' => 'italic' ],
                    [ 'title' => 'Underline', 'icon' => 'underline', 'format' => 'underline' ],
                    [ 'title' => 'Strikethrough', 'icon' => 'strikethrough', 'format' => 'strikethrough' ],
                    [ 'title' => 'Superscript', 'icon' => 'superscript', 'format' => 'superscript' ],
                    [ 'title' => 'Subscript', 'icon' => 'subscript', 'format' => 'subscript' ],
                    [ 'title' => 'Code', 'icon' => 'code', 'format' => 'code' ],
                ]
            ],
            [
                'title' => 'Blocks',
                'items' => [
                    [ 'title' => 'Paragraph', 'format' => 'p' ],
                    [ 'title' => 'Blockquote', 'format' => 'blockquote' ],
                    [ 'title' => 'Div', 'format' => 'div' ],
                    [ 'title' => 'Pre', 'format' => 'pre' ],
                    [ 'title' => 'Noindex', 'format' => 'noindex' ],
                ]
            ],
            [
                'title' => 'Alignment',
                'items' => [
                    [ 'title' => 'Left', 'icon' => 'alignleft', 'format' => 'alignleft' ],
                    [ 'title' => 'Center', 'icon' => 'aligncenter', 'format' => 'aligncenter' ],
                    [ 'title' => 'Right', 'icon' => 'alignright', 'format' => 'alignright' ],
                    [ 'title' => 'Justify', 'icon' => 'alignjustify', 'format' => 'alignjustify' ],
                ]
            ]
        ]
    ]
];