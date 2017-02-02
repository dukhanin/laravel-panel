<?php
$value = $form->getInputValue($field['key']);
$errors = $form->getFieldErrors($field['key']);

if ( ! empty( $data )) {
    $value = $data;
}
?>
<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">
        <div class="form-control" style="border: 0;">
            {!! $value !!}
        </div>


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