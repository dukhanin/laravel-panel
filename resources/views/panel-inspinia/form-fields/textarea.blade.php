<?php
$errors = $form->fieldErrors($field['key']);
?>


<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">
        {!! html_tag(
            'textarea.form-control', [
            'rows' => 8
            ],
            array_except($field, ['key', 'type', 'label']),
            [
                'name' => $form->htmlInputName($field['key']),
                'content' => e( strval($form->inputValue($field['key'])) )
            ]
        ) !!}

        @if ( ! empty( $errors ) )
            <div class="error-text">
                @foreach($errors as $error)
                    <span class="help-block">
                        {{ $error }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>