<?php
$errors = $form->getFieldErrors($field['key']);
?>

<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">
        {!! html_tag(
            'input.form-control',
            array_except($field, ['key', 'type', 'label']),
            [
                'attributes.type' => 'password',
                'attributes.name' => $form->getHtmlInputName($field['key']),
                'attributes.value' => $form->getInputValue($field['key'])
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