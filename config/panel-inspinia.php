<?php

return [
    'views'     => 'panel-inspinia',
    'layout'    => 'panel-inspinia.layout',
    'labels'    => [
        'list-empty'        => 'panel.labels.list-empty',
        'move-to'           => 'panel.labels.move-to',
        'sort-up'           => 'panel.labels.sort-up',
        'sort-down'         => 'panel.labels.sort-down',
        'validation-failed' => 'panel.labels.validation-failed',
        'choose'            => 'panel.labels.choose',
        'name'              => 'panel.labels.name'
    ],
    'confirm'   => [
        'default'      => 'panel.confirm.default',
        'move-to'      => 'panel.confirm.move-to',
        'delete'       => 'panel.confirm.delete',
        'group-delete' => 'panel.confirm.group-delete',
    ],
    'responses' => [
        'success' => 'panel.responses.success',
        'error'   => 'panel.responses.error'
    ],
    'actions'   => [
        'create'        => [
            'class'  => 'btn btn-default btn-sm',
            'label'  => 'panel.actions.create',
            'icon'   => 'fa fa-file-o',
            'action' => 'create',
        ],
        'edit'          => [
            'class'     => 'btn btn-default btn-xs',
            'label'     => 'panel.actions.edit',
            'icon'      => 'fa fa-edit',
            'icon-only' => true,
            'action'    => 'edit'
        ],
        'delete'        => [
            'class'     => 'btn btn-danger btn-xs',
            'label'     => 'panel.actions.delete',
            'icon'      => 'fa fa-trash-o',
            'icon-only' => true,
            'action'    => 'delete',
            'confirm'   => 'panel.confirm.delete'
        ],
        'enable'        => function ($panel, $model) {
            $key = method_exists($panel, 'getEnabledKey') ? $panel->enabledKey() : 'enabled';

            if ($model->{$key}) {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.disable',
                    'icon'      => 'fa fa-eye',
                    'icon-only' => true,
                    'action'    => 'disable'
                ];
            } else {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.enable',
                    'icon'      => 'fa fa-eye-slash',
                    'icon-only' => true,
                    'action'    => 'enable'
                ];
            }
        },
        'disable'       => function ($panel, $model) {
            $key = method_exists($panel, 'getDisabledKey') ? $panel->disabledKey() : 'disabled';

            if ($model->{$key}) {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.enable',
                    'icon'      => 'fa fa-eye-slash',
                    'icon-only' => true,
                    'action'    => 'enable',
                ];
            } else {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.disable',
                    'icon'      => 'fa fa-eye',
                    'icon-only' => true,
                    'action'    => 'disable',
                ];
            }
        },
        'append'        => function ($panel, $model) {
            return [
                'class'     => 'btn btn-default btn-xs',
                'label'     => 'panel.actions.append',
                'icon'      => 'fa fa-plus',
                'icon-only' => true,
                'action'    => 'create'
            ];
        },
        'group-enable'  => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-enable',
            'icon'      => 'fa fa-eye',
            'icon-only' => true,
            'action'    => 'groupEnable'
        ],
        'group-disable' => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-disable',
            'icon'      => 'fa fa-eye-slash',
            'icon-only' => true,
            'action'    => 'groupDisable'
        ],
        'group-delete'  => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-delete',
            'icon'      => 'fa fa-trash-o',
            'icon-only' => true,
            'confirm'   => 'panel.confirm.group-delete',
            'action'    => 'groupDelete'
        ]
    ],
    'buttons'   => [
        'submit'  => [
            'class' => 'btn-primary',
            'type'  => 'submit',
            'label' => 'panel.buttons.submit',
            'icon'  => 'submit',
        ],
        'apply'   => [
            'class' => 'btn-primary',
            'type'  => 'submit',
            'name'  => '_apply',
            'label' => 'panel.buttons.apply',
            'icon'  => 'apply',
        ],
        'cancel'  => [
            'class' => 'btn-default',
            'type'  => 'button',
            'label' => 'panel.buttons.cancel',
            'icon'  => 'cancel'
        ],
        'default' => [

        ]
    ]
];