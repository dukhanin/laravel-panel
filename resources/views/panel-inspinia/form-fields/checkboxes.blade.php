<?php
global $checkboxesIndex;

$value = $form->inputValue($field['key']);
$errors = $form->fieldErrors($field['key']);
$disabled = isset($disabled) ? $disabled : false;
$state = isset($state) ? $state : false;

if (!isset($options) || !is_array($options) && !$options instanceof Illuminate\Support\Collection) {
    $options = [];
}

if ($value instanceof Illuminate\Database\Eloquent\Relations\Relation) {
    $value = $value->get();
}

if ($value instanceof Illuminate\Database\Eloquent\Collection) {
    $value = $value->modelKeys();
}

if (!is_array($value)) {
    $value = [];
}
?>


<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">
        @foreach($options as $optionKey => $optionLabel)
            <div class="checkbox @if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif">
                <input type="checkbox"
                       id="checkbox-{{ ++$checkboxesIndex }}"
                       name="{{  $form->htmlInputName($field['key']) }}[]"
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