<?php

return [
    'views'     => 'panel-bootstrap',
    'layout'    => 'panel-bootstrap.layout',
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
            'action' => 'create',
            'icon'   => 'fa fa-file-o'
        ],
        'edit'          => [
            'class'     => 'btn btn-default btn-xs',
            'label'     => 'panel.actions.edit',
            'action'    => 'edit',
            'icon'      => 'fa fa-edit',
            'icon-only' => true
        ],
        'delete'        => [
            'class'     => 'btn btn-danger btn-xs',
            'label'     => 'panel.actions.delete',
            'action'    => 'delete',
            'icon'      => 'fa fa-trash-o',
            'icon-only' => true,
            'confirm'   => 'panel.confirm.delete'
        ],
        'enable'        => function ($panel, $model) {
            $key = method_exists($panel, 'getEnabledKey') ? $panel->enabledKey() : 'enabled';

            if ($model->{$key}) {
                return [
                    'class'         => 'btn btn-default btn-xs',
                    'label'         => 'panel.actions.disable',
                    'action'        => 'disable',
                    'icon'          => 'fa fa-eye',
                    'icon-on-hover' => 'fa fa-eye-slash',
                    'icon-only'     => true
                ];
            } else {
                return [
                    'class'         => 'btn btn-default btn-xs',
                    'label'         => 'panel.actions.enable',
                    'action'        => 'enable',
                    'icon'          => 'fa fa-eye-slash',
                    'icon-on-hover' => 'fa fa-eye',
                    'icon-only'     => true
                ];
            }
        },
        'disable'       => function ($panel, $model) {
            $key = method_exists($panel, 'getDisabledKey') ? $panel->disabledKey() : 'disabled';

            if ($model->{$key}) {
                return [
                    'class'         => 'btn btn-default btn-xs',
                    'label'         => 'panel.actions.enable',
                    'action'        => 'enable',
                    'icon'          => 'fa fa-eye-slash',
                    'icon-on-hover' => 'fa fa-eye',
                    'icon-only'     => true
                ];
            } else {
                return [
                    'class'         => 'btn btn-default btn-xs',
                    'label'         => 'panel.actions.disable',
                    'action'        => 'disable',
                    'icon'          => 'fa fa-eye',
                    'icon-on-hover' => 'fa fa-eye-slash',
                    'icon-only'     => true
                ];
            }
        },
        'append'        => function ($panel, $model) {
            return [
                'class'     => 'btn btn-default btn-xs',
                'label'     => 'panel.actions.append',
                'action'    => 'create',
                'icon'      => 'fa fa-plus',
                'icon-only' => true
            ];
        },
        'group-enable'  => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-enable',
            'action'    => 'groupEnable',
            'icon'      => 'fa fa-eye',
            'icon-only' => true
        ],
        'group-disable' => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-disable',
            'action'    => 'groupDisable',
            'icon'      => 'fa fa-eye-slash',
            'icon-only' => true
        ],
        'group-delete'  => [
            'class'     => 'btn btn-default btn-sm',
            'label'     => 'panel.actions.group-delete',
            'action'    => 'groupDelete',
            'icon'      => 'fa fa-trash-o',
            'icon-only' => true,
            'confirm'   => 'panel.confirm.group-delete'
        ]
    ],
    'buttons'   => [
        'submit'  => [
            'class' => 'btn-primary',
            'type'  => 'submit',
            'label' => 'panel.buttons.submit',
            'icon'  => 'submit'
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