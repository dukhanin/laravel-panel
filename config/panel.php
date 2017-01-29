<?php

return [
    'views'   => 'panel-bootstrap',
    'layout'  => 'layouts.app',
    'actions' => [
        'create'        => [
            'class' => 'btn btn-default btn-sm',
            'label' => 'panel.actions.create',
            'icon'  => 'fa fa-file-o'
        ],
        'edit'          => [
            'class'     => 'btn btn-default btn-xs',
            'label'     => 'panel.actions.edit',
            'icon'      => 'fa fa-edit',
            'icon-only' => true
        ],
        'delete'        => [
            'class'     => 'btn btn-danger btn-xs',
            'label'     => 'panel.actions.delete',
            'icon'      => 'fa fa-trash-o',
            'icon-only' => true,
            'confirm'   => 'panel.confirm.delete'
        ],
        'enable'        => function ($model, $panel) {
            $key = method_exists($panel, 'getEnabledKey') ? $panel->getEnabledKey() : 'enabled';

            if ($model->{$key}) {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.disable',
                    'icon'      => 'fa fa-eye',
                    'url'       => urlbuilder($panel->getUrl())->append([ 'disable', $model->id ])->compile(),
                    'icon-only' => true
                ];
            } else {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.enable',
                    'icon'      => 'fa fa-eye-slash',
                    'icon-only' => true
                ];
            }
        },
        'disable'       => function ($model, $panel) {
            $key = method_exists($panel, 'getDisabledKey') ? $panel->getDisabledKey() : 'disabled';

            if ($model->{$key}) {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.enable',
                    'icon'      => 'fa fa-eye-slash',
                    'url'       => urlbuilder($panel->getUrl())->append([ 'enable', $model->id ])->compile(),
                    'icon-only' => true
                ];
            } else {
                return [
                    'class'     => 'btn btn-default btn-xs',
                    'label'     => 'panel.actions.disable',
                    'icon'      => 'fa fa-eye',
                    'icon-only' => true
                ];
            }
        },
        'append'        => function ($model, $panel) {
            return [
                'class'     => 'btn btn-default btn-xs',
                'label'     => 'panel.actions.append',
                'icon'      => 'fa fa-plus',
                'icon-only' => true,
                'url'       => urlbuilder($panel->getUrl())->append('create')->query([
                    $panel->getRequestAttributeName('appendTo') => $model->id
                ])->compile()
            ];
        },
        'group-enable'  => [
            'class'      => 'btn btn-default btn-sm',
            'label'      => 'panel.actions.group-enable',
            'icon'       => 'fa fa-eye',
            'icon-only'  => true,
            'attributes' => [ ]
        ],
        'group-disable' => [
            'class'      => 'btn btn-default btn-sm',
            'label'      => 'panel.actions.group-disable',
            'icon'       => 'fa fa-eye-slash',
            'icon-only'  => true,
            'attributes' => [ ]
        ],
        'group-delete'  => [
            'class'      => 'btn btn-default btn-sm',
            'label'      => 'panel.actions.group-delete',
            'icon'       => 'fa fa-trash-o',
            'icon-only'  => true,
            'confirm'    => 'panel.confirm.group-delete',
            'attributes' => [ ]
        ]
    ],
    'buttons' => [
        'submit'  => [
            'class'      => 'btn-primary',
            'type'       => 'submit',
            'label'      => 'panel.buttons.submit',
            'icon'       => 'submit',
            'attributes' => [ ]
        ],
        'apply'   => [
            'class'      => 'btn-primary',
            'type'       => 'submit',
            'name'       => '_apply',
            'label'      => 'panel.buttons.apply',
            'icon'       => 'apply',
            'attributes' => [
                'name' => 'apply'
            ]
        ],
        'cancel'  => [
            'class'      => 'btn-default',
            'type'       => 'button',
            'label'      => 'panel.buttons.cancel',
            'icon'       => 'cancel',
            'attributes' => [ ]
        ],
        'default' => [

        ]
    ]
];