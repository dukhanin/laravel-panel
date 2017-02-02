<?php
global $checkboxesIndex;

$value    = $form->getInputValue($field['key']);
$errors   = $form->getFieldErrors($field['key']);
$disabled = ! empty( $disabled );
$state    = isset($state) ? $state : false;

if ( ! is_array($options) && ! $options instanceof Illuminate\Support\Collection) {
    $options = [ ];
}

if ($value instanceof Illuminate\Database\Eloquent\Collection) {
    $value = $value->modelKeys();
}
?>


<div class="checkbox @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        @foreach($options as $optionKey => $optionLabel)
            <div class="checkbox @if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif">
                <input type="checkbox"
                       id="checkbox-{{ ++$checkboxesIndex }}"
                       name="{{  $form->getHtmlInputName($field['key']) }}[]"
                       value="{{ $optionKey }}"
                       @if( $disabled ) disabled="" @endif
                       @if( in_array($optionKey, $value) ) checked @endif
                        >
                <label for="checkbox-{{ $checkboxesIndex }}"> <i></i> {{ $optionLabel }}</label>
            </div>
        @endforeach

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