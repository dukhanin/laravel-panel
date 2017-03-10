<?php

return [
    'types' => [
        'image' => [
            'formats'    => [
                'jpeg',
                'jpg',
                'gif',
                'png'

            ],
            'extensions' => [
                'jpeg',
                'jpg',
                'gif',
                'png'
            ],
            'fake' => 'img/fake.png'
        ],

        'video' => [
            'formats'    => [
                'x-ms-asf',
                'avi',
                'msvideo',
                'x-msvideo',
                'divx',
                'flc',
                'fli',
                'x-flv',
                'mp4v-es',
                'mp4',
                'x-matroska',
                'quicktime',
                'x-sgi-movie',
                'mp4',
                'mpeg',
                'ogg',
                'vnd.rn-realvideo',
                'webm',
                'x-ms-wm',
                'x-ms-wmv',
                'octet-stream',
                'x-ms-wmx',
                'x-ms-wvx',
                'x-xvid'
            ],
            'extensions' => [
                'asf',
                'asr',
                'asx',
                'avi',
                'divx',
                'flc',
                'fli',
                'flv',
                'm4p',
                'm4v',
                'mkv',
                'mov',
                'movie',
                'mpa',
                'mpe',
                'mpeg',
                'mpg',
                'mpg4',
                'mpv',
                'mpv2',
                'ogv',
                'qt',
                'rv',
                'webm',
                'wm',
                'wmv',
                'wmx',
                'wmx',
                'wvx',
                'xvid'
            ]
        ],

        'audio' => [
            'formats'    => [
                'x-aiff',
                'mpeg',
                'midi',
                'x-realaudio',
                'x-pn-realaudio',
                'x-pn-realaudio-plugin',
                'x-redhat-package-manager',
                'x-wav',
                'x-ms-wax',
                'x-ms-wma'
            ],
            'extensions' => [
                'm4a',
                'midi',
                'mid',
                'mka',
                'mp2',
                'mp3',
                'mpga',
                'oga',
                'ra',
                'ram',
                'rm',
                'rpm',
                'wav',
                'wax',
                'wma'
            ]
        ],

        'document' => [
            'formats'    => [
                'plain',
                'msword',
                'vnd.openxmlformats-officedocument.wordprocessingml.document',
                'vnd.ms-excel',
                'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'rtf',
                'csv',
                'pdf',
                'vnd.openxmlformats-officedocument.presentationml.slideshow',
                'vnd.oasis.opendocument.text',
                'vnd.ms-powerpoint',
                'vnd.openxmlformats-officedocument.presentationml.presentation',
                'html',
                'xhtml+xml',
                'xml',
                'zip',
                'x-compressed-zip',
                'x-rar-compressed'
            ],
            'extensions' => [
                'txt',
                'doc',
                'docx',
                'xls',
                'xlsx',
                'rtf',
                'csv',
                'pdf',
                'ppsx',
                'odt',
                'ppt',
                'pptx',
                'htm',
                'html',
                'xhtml',
                'xml',
                'zip',
                'rar'
            ]
        ]
    ]
];