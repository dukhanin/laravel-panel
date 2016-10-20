<?php
global $checkboxesIndex;

$errors = $form->getFieldErrors($field['key']);
$state    = isset($state) ? $state : false;
$icon     = isset($icon) ? $icon : false;
?>

<div class="checkbox @if( ! empty($errors) )  has-error @endif">
    <input type="hidden" name="{{ $form->getHtmlInputName($field['key']) }}" value="0"/>

    <label class="@if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif">
        <input type="checkbox"
               id="checkbox-{{ ++$checkboxesIndex }}"
               name="{{  $form->getHtmlInputName($field['key']) }}"
               value="1"
               @if( $form->getInputValue($field['key']) ) checked @endif
                />
        <i class="{{ $icon }}"></i> {{ $label }}
    </label>

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