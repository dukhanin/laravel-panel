<?php
$errors = $form->getFieldErrors( $field['key'] );
?>


<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">
        {!! html_tag(
            'textarea.form-control', [
            'attributes.rows' => 8
            ],
            array_except($field, ['key', 'type', 'label']),
            [
                'attributes.name' => $form->getHtmlInputName($field['key']),
                'content' => e( strval($form->getInputValue($field['key'])) )
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